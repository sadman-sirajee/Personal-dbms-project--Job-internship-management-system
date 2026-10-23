<?php
/**
 * Job/Internship Management - Detailed View
 * Job/Internship Management System
 */
require_once __DIR__ . '/../config/database.php';
require_login();

$user = current_user();
$isAdmin = is_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid listing ID.');
    header("Location: " . base_url('jobs/index.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    set_flash('danger', 'Listing not found.');
    header("Location: " . base_url('jobs/index.php'));
    exit;
}

// If user is candidate, check if already applied
$userApplication = null;
if (!$isAdmin) {
    $checkStmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? AND job_id = ? LIMIT 1");
    $checkStmt->execute([$user['id'], $job['id']]);
    $userApplication = $checkStmt->fetch();
}

// If admin, check application count
$appCount = 0;
if ($isAdmin) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ?");
    $countStmt->execute([$job['id']]);
    $appCount = (int)$countStmt->fetchColumn();
}

$page_title = htmlspecialchars($job['title']) . " - Details";
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb / Back button -->
            <div class="mb-3">
                <a href="<?= base_url('jobs/index.php') ?>" class="text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Back to All Listings
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                        <div>
                            <div class="mb-2">
                                <span class="badge <?= ($job['type'] === 'Job') ? 'badge-type-job' : 'badge-type-internship' ?> fs-6">
                                    <?= htmlspecialchars($job['type']) ?>
                                </span>
                                <?php if (!empty($job['deadline'])): ?>
                                    <?php $isPast = strtotime($job['deadline']) < strtotime(date('Y-m-d')); ?>
                                    <span class="badge <?= $isPast ? 'bg-danger' : 'bg-light text-dark border' ?> ms-2">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Deadline: <?= date('M d, Y', strtotime($job['deadline'])) ?>
                                        <?= $isPast ? '(Expired)' : '' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h2 class="fw-bold text-dark mb-1"><?= htmlspecialchars($job['title']) ?></h2>
                            <h5 class="text-secondary fw-semibold mb-0">
                                <i class="bi bi-building me-1"></i> <?= htmlspecialchars($job['company']) ?>
                            </h5>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <?php if ($isAdmin): ?>
                                <a href="<?= base_url('applications/index.php?job_id=' . $job['id']) ?>" class="btn btn-outline-primary btn-sm" title="Review Applications">
                                    <i class="bi bi-people me-1"></i> Applicants (<?= $appCount ?>)
                                </a>
                                <a href="<?= base_url('jobs/edit.php?id=' . $job['id']) ?>" class="btn btn-warning text-dark btn-sm">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </a>
                                <a href="<?= base_url('jobs/delete.php?id=' . $job['id']) ?>" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </a>
                            <?php else: ?>
                                <?php if ($userApplication): ?>
                                    <span class="badge bg-success px-3 py-2 fs-6">
                                        <i class="bi bi-check-circle-fill me-1"></i> Applied (<?= htmlspecialchars($userApplication['status']) ?>)
                                    </span>
                                <?php else: ?>
                                    <a href="<?= base_url('applications/apply.php?job_id=' . $job['id']) ?>" class="btn btn-primary px-3 shadow-sm">
                                        <i class="bi bi-send-fill me-1"></i> Apply Now
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Location and Date Meta -->
                    <div class="row g-3 py-3 px-3 bg-light rounded-3 mb-4">
                        <div class="col-sm-6">
                            <div class="text-muted small">Location</div>
                            <div class="fw-bold text-dark">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <?= htmlspecialchars($job['location'] ?: 'Not Specified') ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Date Posted</div>
                            <div class="fw-bold text-dark">
                                <i class="bi bi-clock-history text-primary me-1"></i>
                                <?= date('F d, Y - h:i A', strtotime($job['created_at'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <h5 class="fw-bold text-dark mb-3">Role Overview &amp; Requirements</h5>
                    <div class="p-3 bg-white border rounded-3 text-secondary mb-4" style="white-space: pre-line; line-height: 1.7;">
                        <?= !empty($job['description']) ? htmlspecialchars($job['description']) : '<em class="text-muted">No description provided for this listing.</em>' ?>
                    </div>

                    <!-- Candidate Application Status or Action Banner -->
                    <?php if (!$isAdmin): ?>
                        <?php if ($userApplication): ?>
                            <div class="alert alert-success d-flex flex-column flex-sm-row align-items-sm-center justify-content-between p-3 rounded-3 mb-0">
                                <div class="mb-2 mb-sm-0">
                                    <strong class="d-block"><i class="bi bi-check2-circle me-1"></i> You have applied for this position!</strong>
                                    <span class="small text-muted">
                                        Submitted on <?= date('F d, Y', strtotime($userApplication['applied_at'])) ?>. Current review status: 
                                        <strong><?= htmlspecialchars($userApplication['status']) ?></strong>
                                    </span>
                                </div>
                                <a href="<?= base_url('applications/my_applications.php') ?>" class="btn btn-outline-success btn-sm text-nowrap">
                                    <i class="bi bi-list-check me-1"></i> View My Applications
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="card bg-light border-0 rounded-3 p-4 text-center">
                                <h5 class="fw-bold text-dark mb-1">Ready to take the next step in your career?</h5>
                                <p class="text-muted small mb-3">Submit your phone number, portfolio/resume, and personalized pitch to apply for this opening.</p>
                                <div>
                                    <a href="<?= base_url('applications/apply.php?job_id=' . $job['id']) ?>" class="btn btn-primary px-4 py-2 shadow-sm">
                                        <i class="bi bi-send-fill me-1"></i> Apply for this Position
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Listing ID: #<?= $job['id'] ?></span>
                    <a href="<?= base_url('jobs/index.php') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Return to Listings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
