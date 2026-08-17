<?php
$pageTitle = 'Edit User';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDBConnection();
$userId = intval($_GET['id'] ?? 0);

if (!$userId) {
    setFlashMessage('error', 'Invalid user ID.');
    redirect('/LeadManagement/admin/users/index.php');
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'User not found.');
    redirect('/LeadManagement/admin/users/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? '';

        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($email)) $errors[] = 'Email is required.';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (empty($role) || !in_array($role, ['admin', 'user'])) $errors[] = 'Valid role is required.';

        if (empty($errors)) {
            $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $userId]);
            if ($checkStmt->fetch()) {
                $errors[] = 'A user with this email already exists.';
            }
        }

        if (empty($errors)) {
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $errors[] = 'Password must be at least 6 characters.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET name=?, email=?, password=?, phone=?, role=?, status=?, updated_at=NOW() WHERE id=?");
                    $stmt->execute([$name, $email, $hashedPassword, $phone, $role, $status, $userId]);
                }
            } else {
                $stmt = $db->prepare("UPDATE users SET name=?, email=?, phone=?, role=?, status=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$name, $email, $phone, $role, $status, $userId]);
            }

            if (empty($errors)) {
                setFlashMessage('success', 'User updated successfully.');
                redirect('/LeadManagement/admin/users/index.php');
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="content">
        <div class="page-header">
            <h4><i class="bi bi-pencil-square me-2"></i>Edit User #<?= $userId ?></h4>
            <a href="/LeadManagement/admin/users/index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                <li><?= escape($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="form-container" style="max-width: 600px;">
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= escape($user['name']) ?>" placeholder="Enter full name" required>
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= escape($user['email']) ?>" placeholder="user@example.com" required>
                    </div>
                    <div class="col-12">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= escape($user['phone'] ?? '') ?>" placeholder="e.g. 9876543210">
                    </div>
                    <div class="col-12">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current" minlength="6">
                        <small class="text-muted">Leave blank to keep current password. Minimum 6 characters if changing.</small>
                    </div>
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update User</button>
                        <a href="/LeadManagement/admin/users/index.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
