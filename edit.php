<?php
/**
 * User Management - Update / Edit User
 * Job/Internship Management System
 */
require_once __DIR__ . '/../config/database.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid user ID.');
    header("Location: " . base_url('users/index.php'));
    exit;
}

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('danger', 'User not found.');
    header("Location: " . base_url('users/index.php'));
    exit;
}

$errors = [];
$name = $user['name'];
$email = $user['email'];
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? 'User');

    // Validation
    if (empty($name)) {
        $errors[] = "Name field is required.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Name cannot exceed 100 characters.";
    }

    if (empty($email)) {
        $errors[] = "Email field is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        // Check for duplicate email excluding this user
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $checkStmt->execute([$email, $id]);
        if ($checkStmt->fetch()) {
            $errors[] = "This email is already in use by another user.";
        }
    }

    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    }

    if (!in_array($role, ['Admin', 'User'])) {
        $errors[] = "Invalid role selected.";
    }

    // Update if no errors
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                // Update with new hashed password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
                $updateStmt->execute([$name, $email, $hashedPassword, $role, $id]);
            } else {
                // Update without modifying password
                $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
                $updateStmt->execute([$name, $email, $role, $id]);
            }

            // Update session name if editing self
            if ($id == $_SESSION['user_id']) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
            }

            set_flash('success', "User '{$name}' updated successfully!");
            header("Location: " . base_url('users/index.php'));
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database update error: " . htmlspecialchars($e->getMessage());
        }
    }
}

$page_title = "Edit User - #" . $user['id'];
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Breadcrumb / Back button -->
            <div class="mb-3">
                <a href="<?= base_url('users/index.php') ?>" class="text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Back to User List
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-pencil-square text-warning me-2"></i>Edit User: <?= htmlspecialchars($user['name']) ?>
                        </h4>
                        <span class="badge bg-warning-subtle text-warning">CRUD: UPDATE</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following errors:</h6>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('users/edit.php?id=' . $id) ?>" method="POST" autocomplete="off">
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($name) ?>" required>
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($email) ?>" required>
                        </div>

                        <!-- Change Password (Optional) -->
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Leave blank to keep existing password">
                            <div class="form-text text-muted">Only enter a value if you wish to reset this user's password.</div>
                        </div>

                        <!-- System Role -->
                        <div class="mb-4">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="User" <?= ($role === 'User') ? 'selected' : '' ?>>User (Standard Member)</option>
                                <option value="Admin" <?= ($role === 'Admin') ? 'selected' : '' ?>>Admin (System Administrator)</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="<?= base_url('users/index.php') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning px-4 text-dark fw-semibold">
                                <i class="bi bi-save me-1"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
