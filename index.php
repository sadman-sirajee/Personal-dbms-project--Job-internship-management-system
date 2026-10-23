<?php
/**
 * Main Landing Page
 * Job/Internship Management System
 */
require_once __DIR__ . '/config/database.php';

// If user is already logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: " . base_url('dashboard.php'));
    exit;
}

$page_title = "Welcome - Job/Internship Portal";
include_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row align-items-center py-5">
        <div class="col-lg-7 text-center text-lg-start mb-5 mb-lg-0">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-6 mb-3 fw-semibold">
                <i class="bi bi-stars me-1"></i> Academic Project &amp; CRUD Demonstration
            </span>
            <h1 class="display-4 fw-bold lh-1 mb-3 text-dark">
                Job &amp; Internship <br><span class="text-primary">Management System</span>
            </h1>
            <p class="lead text-secondary mb-4">
                A complete web-based CRUD application built with PHP, MySQL, and Bootstrap. 
                Featuring role-based authentication, user administration, job/internship listing management, 
                real-time search, and type-based filtering.
            </p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                <a href="<?= base_url('login.php') ?>" class="btn btn-primary btn-lg px-4 me-md-2 shadow">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Access System Dashboard
                </a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white p-4 text-center">
                    <i class="bi bi-shield-lock-fill display-4 text-primary"></i>
                    <h4 class="mt-2 mb-0">System Sign-In</h4>
                    <p class="text-secondary small mb-0">Administrator &amp; User Access</p>
                </div>
                <div class="card-body p-4 text-center">
                    <p class="text-muted">
                        Manage users, post job vacancies, manage internship applications, and view analytics in real-time.
                    </p>
                    <div class="alert alert-info small text-start mb-3">
                        <strong>Default Admin Credentials:</strong><br>
                        Email: <code>admin@gmail.com</code><br>
                        Password: <code>admin123</code>
                    </div>
                    <a href="<?= base_url('login.php') ?>" class="btn btn-outline-primary w-100 py-2">
                        Go to Login Page
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Grid -->
    <div class="row g-4 py-4 row-cols-1 row-cols-md-3">
        <div class="col">
            <div class="card h-100 p-3 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-primary fs-1 mb-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="card-title fw-bold">User Management</h5>
                    <p class="card-text text-muted">Complete CRUD functionality: Add new administrators or standard users, view all user records, edit details, and securely delete users.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 p-3 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-success fs-1 mb-3">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <h5 class="card-title fw-bold">Job &amp; Internship CRUD</h5>
                    <p class="card-text text-muted">Create job opportunities and internships with companies, locations, descriptions, and application deadlines. Full update and delete control.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 p-3 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-warning fs-1 mb-3">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5 class="card-title fw-bold">Search &amp; Filtering</h5>
                    <p class="card-text text-muted">Instantly filter listings by Type (Job or Internship) and query across job titles, hiring companies, and work locations simultaneously.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
