<?php
/**
 * User Management - Create User
 * Job/Internship Management System
 */
require_once __DIR__ . '/../config/database.php';
require_admin();

$errors = [];
$name = '';
$email = '';
$role = 'User';

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
        // Check for duplicate email
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $errors[] = "This email address is already registered. Please choose another.";
        }
    }

    if (empty($password)) {
        $errors[] = "Password field is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (!in_array($role, ['Admin', 'User'])) {
        $errors[] = "Invalid role selected. Must be either Admin or User.";
    }

    // If no errors, insert user
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$name, $email, $hashedPassword, $role]);

            set_flash('success', "User '{$name}' was successfully created!");
            header("Location: " . base_url('users/index.php'));
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}

$page_title = "Add New User";
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Breadcrumbs / Back button -->
            <div class="mb-3">
                <a href="<?= base_url('users/index.php') ?>" class="text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Back to User List
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-person-plus-fill text-primary me-2"></i>Add New User
                        </h4>
                        <span class="badge bg-primary-subtle text-primary">CRUD: CREATE</span>
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

                    <form action="<?= base_url('users/create.php') ?>" method="POST" autocomplete="off">
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($name) ?>" placeholder="e.g. John Doe" required>
                            <div class="form-text">User's full real or professional name.</div>
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($email) ?>" placeholder="e.g. user@example.com" required>
                            <div class="form-text">Must be unique across all system users.</div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Minimum 6 characters" required>
                            <div class="form-text">Will be securely hashed using PHP password_hash (bcrypt).</div>
                        </div>

                        <!-- System Role -->
                        <div class="mb-4">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="User" <?= ($role === 'User') ? 'selected' : '' ?>>User (Standard Member)</option>
                                <option value="Admin" <?= ($role === 'Admin') ? 'selected' : '' ?>>Admin (System Administrator)</option>
                            </select>
                            <div class="form-text">Designate administrator privileges or standard user role.</div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="<?= base_url('users/index.php') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-circle me-1"></i> Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
