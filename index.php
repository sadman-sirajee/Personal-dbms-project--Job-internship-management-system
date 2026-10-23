<?php
/**
 * User Management - Read / View All Users
 * Job/Internship Management System
 */
require_once __DIR__ . '/../config/database.php';
require_admin();

// Search filter
$search = trim($_GET['search'] ?? '');

try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id ASC");
        $term = "%{$search}%";
        $stmt->execute([$term, $term]);
    } else {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
    }
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching users: " . htmlspecialchars($e->getMessage()));
}

$page_title = "User Management - All Users";
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>User Management
            </h1>
            <p class="text-muted mb-0">View, add, edit, and delete system users and administrators.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= base_url('users/create.php') ?>" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Add New User
            </a>
        </div>
    </div>

    <!-- Search Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="<?= base_url('users/index.php') ?>" class="row g-3 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by user name or email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= base_url('users/index.php') ?>" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>All Users (<?= count($users) ?>)
            </h5>
            <span class="badge bg-light text-muted border">CRUD: READ Operation</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registration Date</th>
                            <th class="text-end" style="width: 160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $row): ?>
                                <tr>
                                    <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                                        <?php if ($row['id'] == $_SESSION['user_id']): ?>
                                            <span class="badge bg-info text-dark small" style="font-size: 0.7rem;">Current Session</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($row['email']) ?>" class="text-decoration-none text-muted">
                                            <i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($row['email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($row['role'] === 'Admin') ? 'badge-role-admin' : 'badge-role-user' ?>">
                                            <?= htmlspecialchars($row['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('users/edit.php?id=' . $row['id']) ?>" class="btn btn-outline-warning btn-sm" title="Edit User">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="<?= base_url('users/delete.php?id=' . $row['id']) ?>" class="btn btn-outline-danger btn-sm" title="Delete User">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-people display-4 d-block mb-2 text-secondary opacity-50"></i>
                                        No users found matching your criteria.
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
