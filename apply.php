<?php
/**
 * Job/Internship Application Submission
 * Job/Internship Management System
 */
require_once __DIR__ . '/../config/database.php';
require_login();

$user = current_user();

// Prevent Admins from applying to postings
if (is_admin()) {
    set_flash('info', 'Administrators manage listings and applications rather than submitting them.');
    header("Location: " . base_url('jobs/index.php'));
    exit;
}

$job_id = filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
if (!$job_id) {
    set_flash('danger', 'Invalid or missing listing ID.');
    header("Location: " . base_url('jobs/index.php'));
    exit;
}

// Fetch job details
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? LIMIT 1");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    set_flash('danger', 'Listing not found.');
    header("Location: " . base_url('jobs/index.php'));
    exit;
}

// Check if user has already applied
$checkStmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? AND job_id = ? LIMIT 1");
$checkStmt->execute([$user['id'], $job_id]);
$existingApp = $checkStmt->fetch();

if ($existingApp) {
    set_flash('warning', 'You have already submitted an application for this position. Current status: ' . htmlspecialchars($existingApp['status']));
    header("Location: " . base_url('applications/my_applications.php'));
    exit;
}

$errors = [];
$phone = '';
$cover_note = '';
$resume_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $cover_note = trim($_POST['cover_note'] ?? '');
    $resume_url = trim($_POST['resume_url'] ?? '');

    // Validation
    if (empty($phone)) {
        $errors['phone'] = "Contact phone number is required.";
    } elseif (strlen($phone) < 7 || strlen($phone) > 25) {
        $errors['phone'] = "Please provide a valid phone number (7-25 characters).";
    }

    if (empty($cover_note)) {
        $errors['cover_note'] = "Please provide a brief cover note or pitch for your application.";
    } elseif (strlen($cover_note) < 20) {
        $errors['cover_note'] = "Cover note must be at least 20 characters explaining your interest/fit.";
    }

    if (!empty($resume_url) && !filter_var($resume_url, FILTER_VALIDATE_URL)) {
        $errors['resume_url'] = "Please enter a valid URL (e.g., https://github.com/yourprofile or https://linkedin.com/in/profile).";
    }

    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare("
                INSERT INTO applications (user_id, job_id, phone, cover_note, resume_url, status)
                VALUES (?, ?, ?, ?, ?, 'Pending')
            ");
            $insertStmt->execute([
                $user['id'],
                $job_id,
                $phone,
                $cover_note,
                $resume_url ?: null
            ]);

            set_flash('success', 'Your application for "' . htmlspecialchars($job['title']) . '" at ' . htmlspecialchars($job['company']) . ' was successfully submitted! Status: Pending.');
            header("Location: " . base_url('applications/my_applications.php'));
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors['general'] = "You have already applied for this position.";
            } else {
                $errors['general'] = "Failed to submit application: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

$page_title = "Apply for " . htmlspecialchars($job['title']);
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Back Navigation -->
            <div class="mb-3">
                <a href="<?= base_url('jobs/view.php?id=' . $job['id']) ?>" class="text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Back to Listing Details
                </a>
            </div>

            <!-- Job Summary Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2">
                        <div>
                            <span class="badge <?= ($job['type'] === 'Job') ? 'badge-type-job' : 'badge-type-internship' ?> mb-2">
                                <?= htmlspecialchars($job['type']) ?>
                            </span>
                            <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($job['title']) ?></h3>
                            <h6 class="text-secondary fw-semibold mb-2">
                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($job['company']) ?>
                            </h6>
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars($job['location'] ?: 'Not Specified') ?>
                                <?php if (!empty($job['deadline'])): ?>
                                    <span class="ms-3">
                                        <i class="bi bi-calendar3 me-1 text-primary"></i>Deadline: <?= date('d-m-Y', strtotime($job['deadline'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Form Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="bi bi-send-fill text-primary me-2"></i>Submit Your Application
                    </h4>
                    <p class="text-muted mb-0 small">Please review your contact details and provide a brief statement of interest.</p>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= base_url('applications/apply.php?job_id=' . $job['id']) ?>" novalidate>
                        <!-- Applicant Identity (Read-only) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Applicant Name</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['name']) ?>" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">
                                Contact Phone Number <span class="text-danger">*</span>
                            </label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone" id="phone" 
                                       class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                       placeholder="+880 1711-000000" 
                                       value="<?= htmlspecialchars($phone) ?>" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Direct phone number where hiring managers can reach you.</div>
                        </div>

                        <!-- Resume or Portfolio Link -->
                        <div class="mb-3">
                            <label for="resume_url" class="form-label fw-semibold">
                                Resume, GitHub, or LinkedIn Profile URL <span class="text-muted small fw-normal">(Optional)</span>
                            </label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" name="resume_url" id="resume_url" 
                                       class="form-control <?= isset($errors['resume_url']) ? 'is-invalid' : '' ?>" 
                                       placeholder="https://linkedin.com/in/yourprofile or https://github.com/..." 
                                       value="<?= htmlspecialchars($resume_url) ?>">
                                <?php if (isset($errors['resume_url'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['resume_url']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Link to your online resume, portfolio, GitHub, or LinkedIn profile.</div>
                        </div>

                        <!-- Cover Note / Pitch -->
                        <div class="mb-4">
                            <label for="cover_note" class="form-label fw-semibold">
                                Cover Note / Why are you a good fit? <span class="text-danger">*</span>
                            </label>
                            <textarea name="cover_note" id="cover_note" rows="5" 
                                      class="form-control <?= isset($errors['cover_note']) ? 'is-invalid' : '' ?>" 
                                      placeholder="Describe your background, technical skills, relevant experience, and why you are excited about this role..." required><?= htmlspecialchars($cover_note) ?></textarea>
                            <?php if (isset($errors['cover_note'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['cover_note']) ?></div>
                            <?php else: ?>
                                <div class="form-text">Minimum 20 characters. Highlight key accomplishments and passion for this role.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="<?= base_url('jobs/view.php?id=' . $job['id']) ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
