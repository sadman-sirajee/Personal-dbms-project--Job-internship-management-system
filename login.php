<?php
/**
 * User & Admin Login
 * Job/Internship Management System
 */
require_once __DIR__ . '/config/database.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: " . base_url('dashboard.php'));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in both email and password fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Query user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session id for security against session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            set_flash('success', "Welcome back, " . htmlspecialchars($user['name']) . "! You have successfully logged in.");
            header("Location: " . base_url('dashboard.php'));
            exit;
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    }
}

$page_title = "Login - Job/Internship Portal";
include_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4 mt-3">
                <div class="card-header bg-primary text-white text-center py-4 rounded-top-4">
                    <div class="mb-2">
                        <i class="bi bi-person-circle display-4"></i>
                    </div>
                    <h3 class="fw-bold mb-0">System Login</h3>
                    <p class="small text-white-50 mb-0">Job/Internship Management System</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('login.php') ?>" method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i> Email Address
                            </label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   value="<?= htmlspecialchars($email) ?>" placeholder="e.g. admin@gmail.com" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i> Password
                            </label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                   placeholder="Enter your password" required>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3 mb-2">
                        <p class="text-muted small mb-0">
                            New candidate? 
                            <a href="<?= base_url('register.php') ?>" class="text-primary fw-bold text-decoration-none">
                                <i class="bi bi-person-plus me-1"></i>Create an account to apply
                            </a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
