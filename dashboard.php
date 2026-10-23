<?php
/**
 * Dashboard - Role-Aware (Admin vs Candidate)
 * Job/Internship Management System
 */
require_once __DIR__ . '/config/database.php';
require_login();

$user = current_user();
$isAdmin = is_admin();

try {
    if ($isAdmin) {
        // ADMIN METRICS
        $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalJobs = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE type = 'Job'")->fetchColumn();
        $totalInternships = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE type = 'Internship'")->fetchColumn();
        $totalApplications = (int)$pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
        $pendingApplications = (int)$pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Pending'")->fetchColumn();

        // Recent Jobs
        $stmtRecentJobs = $pdo->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 5");
        $recentJobs = $stmtRecentJobs->fetchAll();

        // Recent Applications
        $stmtRecentApps = $pdo->query("
            SELECT a.*, u.name AS applicant_name, u.email AS applicant_email,
                   j.title AS job_title, j.company, j.type AS job_type
            FROM applications a
            JOIN users u ON a.user_id = u.id
            JOIN jobs j ON a.job_id = j.id
            ORDER BY a.applied_at DESC LIMIT 5
        ");
        $recentApplications = $stmtRecentApps->fetchAll();

        // Recent Users
        $stmtRecentUsers = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        $recentUsers = $stmtRecentUsers->fetchAll();
    } else {
        // CANDIDATE METRICS
        $availableJobs = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE type = 'Job'")->fetchColumn();
        $availableInternships = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE type = 'Internship'")->fetchColumn();

        $stmtUserApps = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
        $stmtUserApps->execute([$user['id']]);
        $myTotalApps = (int)$stmtUserApps->fetchColumn();

        $stmtUserPending = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status = 'Pending'");
        $stmtUserPending->execute([$user['id']]);
        $myPendingApps = (int)$stmtUserPending->fetchColumn();

        $stmtUserAccepted = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status = 'Accepted'");
        $stmtUserAccepted->execute([$user['id']]);
        $myAcceptedApps = (int)$stmtUserAccepted->fetchColumn();

        // Candidate's Recent Applications
        $stmtMyApps = $pdo->prepare("
            SELECT a.*, j.title AS job_title, j.company, j.location, j.type AS job_type, j.deadline
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            WHERE a.user_id = ?
            ORDER BY a.applied_at DESC LIMIT 5
        ");
        $stmtMyApps->execute([$user['id']]);
        $myApplications = $stmtMyApps->fetchAll();

        // Recommended / Recent Vacancies
        $stmtNewOpenings = $pdo->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 5");
        $newOpenings = $stmtNewOpenings->fetchAll();

        // Check which openings the user has already applied for
        $appliedJobIds = [];
        if (!empty($newOpenings)) {
            $appliedStmt = $pdo->prepare("SELECT job_id FROM applications WHERE user_id = ?");
            $appliedStmt->execute([$user['id']]);
            $appliedJobIds = $appliedStmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
} catch (PDOException $e) {
    die("Database query error: " . htmlspecialchars($e->getMessage()));
}

$page_title = $isAdmin ? "Admin Dashboard - Overview" : "Candidate Dashboard - Overview";
include_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">
                <i class="bi bi-speedometer2 text-primary me-2"></i><?= $isAdmin ? 'Administrator Dashboard' : 'Candidate Portal' ?>
            </h1>
            <p class="text-muted mb-0">
                Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong>! 
                You are signed in as <span class="badge <?= $isAdmin ? 'badge-role-admin' : 'badge-role-user' ?>"><?= htmlspecialchars($user['role']) ?></span>.
            </p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <?php if ($isAdmin): ?>
                <a href="<?= base_url('jobs/create.php') ?>" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Listing
                </a>
                <a href="<?= base_url('users/create.php') ?>" class="btn btn-outline-dark shadow-sm">
                    <i class="bi bi-person-plus me-1"></i> Add User
                </a>
                <a href="<?= base_url('applications/index.php') ?>" class="btn btn-outline-primary shadow-sm">
                    <i class="bi bi-file-earmark-person me-1"></i> Applications
                </a>
            <?php else: ?>
                <a href="<?= base_url('jobs/index.php') ?>" class="btn btn-primary shadow-sm">
                    <i class="bi bi-search me-1"></i> Explore Vacancies
                </a>
                <a href="<?= base_url('applications/my_applications.php') ?>" class="btn btn-outline-primary shadow-sm">
                    <i class="bi bi-file-earmark-check me-1"></i> My Applications
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isAdmin): ?>
        <!-- ======================================================== -->
        <!-- ADMIN DASHBOARD CONTENT -->
        <!-- ======================================================== -->

        <!-- 4 Key Stat Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Users -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card bg-primary text-white shadow">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Total Users</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $totalUsers ?></h2>
                            <a href="<?= base_url('users/index.php') ?>" class="text-white text-decoration-none small opacity-75 mt-2 d-inline-block">
                                Manage Users <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Jobs -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card bg-info text-white shadow">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Total Jobs</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $totalJobs ?></h2>
                            <a href="<?= base_url('jobs/index.php?type=Job') ?>" class="text-white text-decoration-none small opacity-75 mt-2 d-inline-block">
                                View Jobs <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Internships -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card bg-success text-white shadow">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Total Internships</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $totalInternships ?></h2>
                            <a href="<?= base_url('jobs/index.php?type=Internship') ?>" class="text-white text-decoration-none small opacity-75 mt-2 d-inline-block">
                                View Internships <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Applications -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card bg-dark text-white shadow">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Applications</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $totalApplications ?></h2>
                            <a href="<?= base_url('applications/index.php') ?>" class="text-white text-decoration-none small opacity-75 mt-2 d-inline-block">
                                <?= $pendingApplications > 0 ? "<strong>{$pendingApplications}</strong> Pending Review" : "Manage Applications" ?> <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-file-earmark-person-fill text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Quick Modules -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i>User Management</h5>
                        <p class="text-muted small mb-3">Add administrators or standard candidates, reset passwords, manage accounts.</p>
                        <a href="<?= base_url('users/index.php') ?>" class="btn btn-sm btn-outline-primary">
                            Open Users <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1"><i class="bi bi-card-checklist text-success me-2"></i>Postings Management</h5>
                        <p class="text-muted small mb-3">Create, edit, or remove job listings and internship openings across companies.</p>
                        <a href="<?= base_url('jobs/index.php') ?>" class="btn btn-sm btn-outline-success">
                            Open Postings <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1"><i class="bi bi-file-earmark-check text-warning me-2"></i>Application Reviews</h5>
                        <p class="text-muted small mb-3">Evaluate candidate pitches, inspect portfolios/resumes, and accept or reject.</p>
                        <a href="<?= base_url('applications/index.php') ?>" class="btn btn-sm btn-outline-warning text-dark">
                            Review Submissions <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables Grid -->
        <div class="row g-4">
            <!-- Recent Applications -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="bi bi-file-earmark-person me-1 text-primary"></i> Recent Candidate Applications
                        </h5>
                        <a href="<?= base_url('applications/index.php') ?>" class="btn btn-outline-primary btn-sm">
                            View All
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentApplications)): ?>
                                        <?php foreach ($recentApplications as $app): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($app['applicant_name']) ?></strong>
                                                    <div class="small text-muted"><?= htmlspecialchars($app['applicant_email']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-primary"><?= htmlspecialchars($app['job_title']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($app['company']) ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($app['status'] === 'Accepted'): ?>
                                                        <span class="badge bg-success">Accepted</span>
                                                    <?php elseif ($app['status'] === 'Reviewed'): ?>
                                                        <span class="badge bg-info text-dark">Reviewed</span>
                                                    <?php elseif ($app['status'] === 'Rejected'): ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= base_url('applications/index.php?search=' . urlencode($app['applicant_email'])) ?>" class="btn btn-light btn-sm text-primary" title="Review">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No applications received yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Postings -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="bi bi-clock-history me-1 text-primary"></i> Recent Vacancies
                        </h5>
                        <a href="<?= base_url('jobs/index.php') ?>" class="btn btn-outline-primary btn-sm">
                            View All
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title &amp; Company</th>
                                        <th>Type</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentJobs)): ?>
                                        <?php foreach ($recentJobs as $job): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($job['title']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($job['company']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge <?= ($job['type'] === 'Job') ? 'badge-type-job' : 'badge-type-internship' ?>">
                                                        <?= htmlspecialchars($job['type']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= base_url('jobs/view.php?id=' . $job['id']) ?>" class="btn btn-light btn-sm text-primary" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="<?= base_url('jobs/edit.php?id=' . $job['id']) ?>" class="btn btn-light btn-sm text-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No vacancies posted yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ======================================================== -->
        <!-- CANDIDATE / USER DASHBOARD CONTENT -->
        <!-- ======================================================== -->

        <!-- 4 Candidate Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card bg-primary text-white shadow">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Available Jobs</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $availableJobs ?></h2>
                            <a href="<?= base_url('jobs/index.php?type=Job') ?>" class="text-white text-decoration-none small opacity-75 mt-2 d-inline-block">
                                Explore Jobs <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card bg-info text-white shadow">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Internships</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $availableInternships ?></h2>
                            <a href="<?= base_url('jobs/index.php?type=Internship') ?>" class="text-white text-decoration-none small opacity-75 mt-2 d-inline-block">
                                Explore Internships <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card bg-warning text-dark shadow">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">My Applications</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $myTotalApps ?></h2>
                            <a href="<?= base_url('applications/my_applications.php') ?>" class="text-dark text-decoration-none small opacity-75 mt-2 d-inline-block">
                                <?= $myPendingApps ?> Pending Review <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-file-earmark-check-fill text-dark opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card bg-success text-white shadow">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-1 opacity-75">Accepted Offers</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $myAcceptedApps ?></h2>
                            <a href="<?= base_url('applications/my_applications.php') ?>" class="text-white text-decoration-none small opacity-75 mt-2 d-inline-block">
                                Track Progress <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-trophy-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Candidate Content Grid -->
        <div class="row g-4">
            <!-- My Recent Applications -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="bi bi-file-earmark-check text-primary me-2"></i>My Recent Applications
                        </h5>
                        <a href="<?= base_url('applications/my_applications.php') ?>" class="btn btn-outline-primary btn-sm">
                            View All
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($myApplications)): ?>
                                        <?php foreach ($myApplications as $app): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= base_url('jobs/view.php?id=' . $app['job_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                                        <?= htmlspecialchars($app['job_title']) ?>
                                                    </a>
                                                    <div class="small text-muted"><?= htmlspecialchars($app['company']) ?></div>
                                                </td>
                                                <td>
                                                    <span class="small text-muted"><?= date('M d, Y', strtotime($app['applied_at'])) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($app['status'] === 'Accepted'): ?>
                                                        <span class="badge bg-success">Accepted</span>
                                                    <?php elseif ($app['status'] === 'Reviewed'): ?>
                                                        <span class="badge bg-info text-dark">Under Review</span>
                                                    <?php elseif ($app['status'] === 'Rejected'): ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= base_url('applications/my_applications.php') ?>" class="btn btn-light btn-sm text-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <p class="text-muted mb-2">You haven't applied for any positions yet.</p>
                                                <a href="<?= base_url('jobs/index.php') ?>" class="btn btn-primary btn-sm">
                                                    Browse Open Positions
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Openings to Apply For -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="bi bi-lightning-charge text-warning me-2"></i>Latest Openings
                        </h5>
                        <a href="<?= base_url('jobs/index.php') ?>" class="btn btn-outline-primary btn-sm">
                            All Vacancies
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Vacancy</th>
                                        <th>Type</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($newOpenings)): ?>
                                        <?php foreach ($newOpenings as $op): ?>
                                            <?php $isApplied = in_array($op['id'], $appliedJobIds); ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= base_url('jobs/view.php?id=' . $op['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                                        <?= htmlspecialchars($op['title']) ?>
                                                    </a>
                                                    <div class="small text-muted">
                                                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($op['company']) ?>
                                                        <span class="mx-1">•</span>
                                                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($op['location'] ?: 'Not Specified') ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge <?= ($op['type'] === 'Job') ? 'badge-type-job' : 'badge-type-internship' ?>">
                                                        <?= htmlspecialchars($op['type']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($isApplied): ?>
                                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                                            <i class="bi bi-check-circle-fill text-success me-1"></i> Applied
                                                        </span>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('applications/apply.php?job_id=' . $op['id']) ?>" class="btn btn-primary btn-sm">
                                                            Apply Now
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No vacancies open right now.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
