<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDBConnection();

$stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

$adminCountStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'");
$adminCountStmt->execute();
$adminCount = $adminCountStmt->fetchColumn();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="content">
        <?php $flash = getFlashMessage(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= escape($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <h4><i class="bi bi-person-gear me-2"></i>Manage Users</h4>
            <a href="/LeadManagement/admin/users/create.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add User
            </a>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td class="fw-medium"><?= escape($u['name']) ?></td>
                            <td><?= escape($u['email']) ?></td>
                            <td><?= escape($u['phone'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'primary' ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($u['status']) ?></span></td>
                            <td><?= formatDate($u['created_at']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/LeadManagement/admin/users/edit.php?id=<?= $u['id'] ?>" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <?php $canDelete = !($u['role'] === 'admin' && $adminCount <= 1); ?>
                                    <form method="POST" action="/LeadManagement/admin/users/delete.php" class="delete-form d-inline" style="margin:0;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger <?= $canDelete ? '' : 'disabled' ?>" title="<?= $canDelete ? 'Delete' : 'Last admin - cannot delete' ?>" <?= $canDelete ? 'onclick="return confirmDelete(\'Are you sure you want to delete this user?\')"' : 'disabled' ?>><i class="bi bi-trash"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
