<?php
$pageTitle = 'Assign Lead';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDBConnection();
$leadId = intval($_GET['id'] ?? 0);

if (!$leadId) {
    setFlashMessage('error', 'Invalid lead ID.');
    redirect('/LeadManagement/admin/leads/index.php');
}

$stmt = $db->prepare("SELECT l.*, u.name as assigned_user_name FROM leads l LEFT JOIN users u ON l.assigned_user_id = u.id WHERE l.id = ? AND l.deleted_at IS NULL");
$stmt->execute([$leadId]);
$lead = $stmt->fetch();

if (!$lead) {
    setFlashMessage('error', 'Lead not found.');
    redirect('/LeadManagement/admin/leads/index.php');
}

$usersStmt = $db->prepare("SELECT id, name FROM users WHERE status = 'active' ORDER BY name");
$usersStmt->execute();
$usersList = $usersStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
        redirect('/LeadManagement/admin/leads/index.php');
    }

    $newUserId = intval($_POST['assigned_user_id'] ?? 0);
    if (!$newUserId) {
        setFlashMessage('error', 'Please select a user.');
        redirect('/LeadManagement/admin/leads/assign.php?id=' . $leadId);
    }

    $newUserStmt = $db->prepare("SELECT name FROM users WHERE id = ? AND status = 'active'");
    $newUserStmt->execute([$newUserId]);
    $newUser = $newUserStmt->fetch();

    if (!$newUser) {
        setFlashMessage('error', 'Selected user not found or inactive.');
        redirect('/LeadManagement/admin/leads/assign.php?id=' . $leadId);
    }

    $oldUserName = $lead['assigned_user_name'] ?? 'Unassigned';

    $stmt = $db->prepare("UPDATE leads SET assigned_user_id = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newUserId, $leadId]);

    createActivity($leadId, $_SESSION['user_id'], 'Lead Assigned', 'Lead assigned from ' . $oldUserName . ' to ' . $newUser['name'] . '.');

    setFlashMessage('success', 'Lead assigned successfully to ' . $newUser['name'] . '.');
    redirect('/LeadManagement/admin/leads/view.php?id=' . $leadId);
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="content">
        <div class="page-header">
            <h4><i class="bi bi-person-plus me-2"></i>Assign Lead</h4>
            <a href="/LeadManagement/admin/leads/view.php?id=<?= $leadId ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Lead
            </a>
        </div>

        <div class="form-container" style="max-width: 500px;">
            <div class="mb-3">
                <label class="form-label fw-medium">Lead</label>
                <p class="form-control-plaintext"><?= escape($lead['customer_name']) ?> (<?= escape($lead['company_name'] ?? '') ?>)</p>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Currently Assigned To</label>
                <p class="form-control-plaintext"><?= escape($lead['assigned_user_name'] ?? 'Unassigned') ?></p>
            </div>
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label for="assigned_user_id" class="form-label">Assign To <span class="text-danger">*</span></label>
                    <select class="form-select" id="assigned_user_id" name="assigned_user_id" required>
                        <option value="">Select User</option>
                        <?php foreach ($usersList as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $lead['assigned_user_id'] == $u['id'] ? 'selected' : '' ?>><?= escape($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Assign Lead</button>
                <a href="/LeadManagement/admin/leads/view.php?id=<?= $leadId ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
