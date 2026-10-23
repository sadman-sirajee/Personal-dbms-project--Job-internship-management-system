<?php
/**
 * Navigation Bar Component
 * Job/Internship Management System
 */

$user = is_logged_in() ? current_user() : null;
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('dashboard.php') ?>">
            <i class="bi bi-briefcase-fill text-primary"></i>
            <span>CareerPulse</span>
            <span class="badge bg-light text-dark fs-6 ms-1 d-none d-sm-inline-block">Job & Internship Portal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <?php if (is_logged_in()): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="<?= base_url('dashboard.php') ?>">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <?php if (is_admin()): ?>
                        <!-- Admin Navigation -->
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_dir === 'users') ? 'active' : '' ?>" href="<?= base_url('users/index.php') ?>">
                                <i class="bi bi-people me-1"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_dir === 'jobs') ? 'active' : '' ?>" href="<?= base_url('jobs/index.php') ?>">
                                <i class="bi bi-briefcase me-1"></i> Job Postings
                            </a>
                        </li>
                        <li class="nav-item">
                            <?php
                            // Fetch pending applications count for Admin badge
                            $navPendingCount = 0;
                            try {
                                $navPendingCount = (int)$pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Pending'")->fetchColumn();
                            } catch (Exception $e) {}
                            ?>
                            <a class="nav-link <?= ($current_dir === 'applications' && $current_page === 'index.php') ? 'active' : '' ?>" href="<?= base_url('applications/index.php') ?>">
                                <i class="bi bi-file-earmark-person me-1"></i> Applications
                                <?php if ($navPendingCount > 0): ?>
                                    <span class="badge bg-warning text-dark rounded-pill ms-1"><?= $navPendingCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Candidate Navigation -->
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_dir === 'jobs') ? 'active' : '' ?>" href="<?= base_url('jobs/index.php') ?>">
                                <i class="bi bi-search me-1"></i> Explore Vacancies
                            </a>
                        </li>
                        <li class="nav-item">
                            <?php
                            // Fetch user's applications count
                            $userAppCount = 0;
                            try {
                                $userAppStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
                                $userAppStmt->execute([$user['id']]);
                                $userAppCount = (int)$userAppStmt->fetchColumn();
                            } catch (Exception $e) {}
                            ?>
                            <a class="nav-link <?= ($current_dir === 'applications') ? 'active' : '' ?>" href="<?= base_url('applications/my_applications.php') ?>">
                                <i class="bi bi-file-earmark-check me-1"></i> My Applications
                                <?php if ($userAppCount > 0): ?>
                                    <span class="badge bg-light text-primary rounded-pill ms-1"><?= $userAppCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3 text-light">
                        <span class="badge bg-secondary p-2 me-1">
                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user['name']) ?>
                        </span>
                        <span class="badge <?= ($user['role'] === 'Admin') ? 'badge-role-admin' : 'badge-role-user' ?> p-2">
                            <?= htmlspecialchars($user['role']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm text-white" href="<?= base_url('logout.php') ?>">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ms-auto d-flex gap-2">
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm px-3" href="<?= base_url('register.php') ?>">
                            <i class="bi bi-person-plus"></i> Sign Up
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3" href="<?= base_url('login.php') ?>">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
