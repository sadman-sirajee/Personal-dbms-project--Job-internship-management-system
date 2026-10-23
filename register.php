<?php
/**
 * Candidate Self-Registration
 * Job/Internship Management System
 */
require_once __DIR__ . '/config/database.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: " . base_url('dashboard.php'));
    exit;
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors['name'] = "Full name is required.";
    } elseif (strlen($name) < 3) {
        $errors['name'] = "Name must be at least 3 characters long.";
    }

    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    } else {
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = "An account with this email address already exists.";
        }
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'User')");
            $insertStmt->execute([$name, $email, $hashedPassword]);
            $newUserId = (int)$pdo->lastInsertId();

            // Auto-login newly registered candidate
            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'User';

            set_flash('success', "Welcome to CareerPulse, " . htmlspecialchars($name) . "! Your candidate account has been created. Start exploring and applying for jobs!");
            header("Location: " . base_url('dashboard.php'));
            exit;
        } catch (PDOException $e) {
            $errors['general'] = "Registration failed: " . htmlspecialchars($e->getMessage());
        }
    }
}

$page_title = "Candidate Registration - CareerPulse";
include_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4 mt-2">
                <div class="card-header bg-primary text-white text-center py-4 rounded-top-4">
                    <div class="mb-2">
                        <i class="bi bi-person-plus-fill display-4"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Candidate Sign Up</h3>
                    <p class="small text-white-50 mb-0">Create an account to apply for jobs & internships</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= htmlspecialchars($errors['general']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('register.php') ?>" method="POST" autocomplete="off" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i> Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Rahim Ahmed" required autofocus>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i> Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control form-control-lg <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="e.g. user@gmail.com" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-lock me-1"></i> Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control form-control-lg <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" name="password" placeholder="At least 6 characters" required>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <i class="bi bi-shield-check me-1"></i> Confirm Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control form-control-lg <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" name="confirm_password" placeholder="Re-type password" required>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Create Account &amp; Sign In
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted small mb-0">
                            Already have an account? 
                            <a href="<?= base_url('login.php') ?>" class="text-primary fw-bold text-decoration-none">Sign In here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
