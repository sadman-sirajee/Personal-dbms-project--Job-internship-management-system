<?php
/**
 * Candidate Portal - My Applications
 * Job/Internship Management System
 */
require_once __DIR__ . '/../config/database.php';
require_login();

$user = current_user();

// Handle application withdrawal (only if Pending)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'withdraw') {
    $app_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
    if ($app_id) {
        // Verify application belongs to current user and is Pending
        $checkStmt = $pdo->prepare("SELECT id, status FROM applications WHERE id = ? AND user_id = ? LIMIT 1");
        $checkStmt->execute([$app_id, $user['id']]);
        $app = $checkStmt->fetch();

        if ($app && $app['status'] === 'Pending') {
            $delStmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
            $delStmt->execute([$app_id]);
            set_flash('success', 'Your application has been safely withdrawn.');
        } else {
            set_flash('danger', 'Unable to withdraw application. Only pending applications may be withdrawn.');
        }
    }
    header("Location: " . base_url('applications/my_applications.php'));
    exit;
}

// Fetch user applications
try {
    $stmt = $pdo->prepare("
        SELECT a.*, j.title AS job_title, j.company, j.location, j.type AS job_type, j.deadline
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.user_id = ?
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$user['id']]);
    $applications = $stmt->fetchAll();

    // Calculate metrics
    $totalApps = count($applications);
    $pendingApps = count(array_filter($applications, fn($a) => $a['status'] === 'Pending'));
    $reviewedApps = count(array_filter($applications, fn($a) => $a['status'] === 'Reviewed'));
    $acceptedApps = count(array_filter($applications, fn($a) => $a['status'] === 'Accepted'));
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$page_title = "My Job Applications";
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-check-fill text-primary me-2"></i>My Applications
            </h1>
            <p class="text-muted mb-0">Track the review status and progress of all your submitted vacancies.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= base_url('jobs/index.php') ?>" class="btn btn-primary shadow-sm">
                <i class="bi bi-search me-1"></i> Browse More Openings
            </a>
        </div>
    </div>

    <!-- Candidate KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-3 text-center">
                    <div class="text-muted small fw-semibold text-uppercase">Total Submitted</div>
                    <div class="display-6 fw-bold text-dark"><?= $totalApps ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-3 text-center">
                    <div class="text-muted small fw-semibold text-uppercase">Pending Review</div>
                    <div class="display-6 fw-bold text-warning"><?= $pendingApps ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-3 text-center">
                    <div class="text-muted small fw-semibold text-uppercase">Reviewed</div>
                    <div class="display-6 fw-bold text-info"><?= $reviewedApps ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-3 text-center">
                    <div class="text-muted small fw-semibold text-uppercase">Accepted</div>
                    <div class="display-6 fw-bold text-success"><?= $acceptedApps ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-list-stars text-primary me-2"></i>Submitted Applications List
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($applications)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Job / Internship</th>
                                <th>Company</th>
                                <th>Type</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td class="ps-4">
                                        <a href="<?= base_url('jobs/view.php?id=' . $app['job_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= htmlspecialchars($app['job_title']) ?>
                                        </a>
                                        <div class="small text-muted">
                                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($app['location'] ?: 'Remote / Unspecified') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-secondary">
                                            <i class="bi bi-building me-1"></i><?= htmlspecialchars($app['company']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($app['job_type'] === 'Job') ? 'badge-type-job' : 'badge-type-internship' ?>">
                                            <?= htmlspecialchars($app['job_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            <i class="bi bi-clock me-1"></i><?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($app['status'] === 'Accepted'): ?>
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="bi bi-check-circle-fill me-1"></i> Accepted
                                            </span>
                                        <?php elseif ($app['status'] === 'Reviewed'): ?>
                                            <span class="badge bg-info text-dark px-3 py-2">
                                                <i class="bi bi-eye-fill me-1"></i> Under Review
                                            </span>
                                        <?php elseif ($app['status'] === 'Rejected'): ?>
                                            <span class="badge bg-danger px-3 py-2">
                                                <i class="bi bi-x-circle-fill me-1"></i> Rejected
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-3 py-2">
                                                <i class="bi bi-hourglass-split me-1"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#noteModal<?= $app['id'] ?>">
                                            <i class="bi bi-file-text me-1"></i> View Note
                                        </button>
                                        <a href="<?= base_url('jobs/view.php?id=' . $app['job_id']) ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <?php if ($app['status'] === 'Pending'): ?>
                                            <form method="POST" action="<?= base_url('applications/my_applications.php') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to withdraw this application?');">
                                                <input type="hidden" name="action" value="withdraw">
                                                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Withdraw Application">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Application Note Modal -->
                                <div class="modal fade" id="noteModal<?= $app['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-file-earmark-text text-primary me-2"></i>Submitted Application Details
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-target="#noteModal<?= $app['id'] ?>" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <strong class="d-block text-secondary small text-uppercase">Position</strong>
                                                    <div class="fw-bold fs-6"><?= htmlspecialchars($app['job_title']) ?> - <?= htmlspecialchars($app['company']) ?></div>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <strong class="d-block text-secondary small text-uppercase">Phone Provided</strong>
                                                        <div><?= htmlspecialchars($app['phone'] ?: 'N/A') ?></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <strong class="d-block text-secondary small text-uppercase">Current Status</strong>
                                                        <div><span class="badge bg-secondary"><?= htmlspecialchars($app['status']) ?></span></div>
                                                    </div>
                                                </div>
                                                <?php if (!empty($app['resume_url'])): ?>
                                                    <div class="mb-3">
                                                        <strong class="d-block text-secondary small text-uppercase">Portfolio / Resume Link</strong>
                                                        <a href="<?= htmlspecialchars($app['resume_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-break">
                                                            <i class="bi bi-link-45deg me-1"></i><?= htmlspecialchars($app['resume_url']) ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong class="d-block text-secondary small text-uppercase mb-1">Your Pitch / Cover Note</strong>
                                                    <div class="p-3 bg-light rounded border text-muted small" style="white-space: pre-line;">
                                                        <?= htmlspecialchars($app['cover_note']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-briefcase display-4 text-muted opacity-50 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">You haven't applied for any positions yet</h5>
                    <p class="text-muted">Explore our latest job and internship openings and apply with just a few clicks!</p>
                    <a href="<?= base_url('jobs/index.php') ?>" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-arrow-right me-1"></i> Browse Openings
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
