<?php
/**
 * User Management - Delete User Confirmation & Action
 * Job/Internship Management System
 */
require_once __DIR__ . '/../config/database.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid user ID.');
    header("Location: " . base_url('users/index.php'));
    exit;
}

// Fetch user to confirm deletion
$stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('danger', 'User not found.');
    header("Location: " . base_url('users/index.php'));
    exit;
}

// Prevent user from deleting their own active account
if ($id == $_SESSION['user_id']) {
    set_flash('danger', 'Action Denied: You cannot delete your own currently logged-in account.');
    header("Location: " . base_url('users/index.php'));
    exit;
}

// Handle POST deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $delStmt->execute([$id]);

        set_flash('success', "User '{$user['name']}' has been successfully deleted.");
        header("Location: " . base_url('users/index.php'));
        exit;
    } catch (PDOException $e) {
        set_flash('danger', "Database error during deletion: " . htmlspecialchars($e->getMessage()));
        header("Location: " . base_url('users/index.php'));
        exit;
    }
}

$page_title = "Delete User Confirmation";
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-danger shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-danger text-white py-3 text-center">
                    <i class="bi bi-exclamation-triangle-fill display-5 d-block mb-1"></i>
                    <h4 class="fw-bold mb-0">Confirm User Deletion</h4>
                    <span class="badge bg-danger-subtle text-white border border-light mt-1">CRUD: DELETE Operation</span>
                </div>
                <div class="card-body p-4 text-center">
                    <h5 class="text-danger fw-bold mb-3">
                        Are you sure you want to delete this user?
                    </h5>
                    <p class="text-muted mb-4">
                        This action is permanent and cannot be undone. All access for this account will be revoked.
                    </p>

                    <!-- User Details Summary Card -->
                    <div class="bg-light p-3 rounded-3 text-start mb-4 border">
                        <div class="row mb-2">
                            <div class="col-4 text-muted fw-semibold">User ID:</div>
                            <div class="col-8">#<?= $user['id'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted fw-semibold">Name:</div>
                            <div class="col-8 fw-bold text-dark"><?= htmlspecialchars($user['name']) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted fw-semibold">Email:</div>
                            <div class="col-8"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                        <div class="row">
                            <div class="col-4 text-muted fw-semibold">Role:</div>
                            <div class="col-8">
                                <span class="badge <?= ($user['role'] === 'Admin') ? 'badge-role-admin' : 'badge-role-user' ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions form with Cancel and Delete buttons -->
                    <form action="<?= base_url('users/delete.php') ?>" method="POST" class="d-flex justify-content-center gap-3">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="confirm_delete" value="1">

                        <a href="<?= base_url('users/index.php') ?>" class="btn btn-outline-secondary px-4 py-2">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold shadow">
                            <i class="bi bi-trash-fill me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
