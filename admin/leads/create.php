<?php
$pageTitle = 'Add New Lead';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDBConnection();

$usersStmt = $db->prepare("SELECT id, name FROM users WHERE status = 'active' ORDER BY name");
$usersStmt->execute();
$usersList = $usersStmt->fetchAll();

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $old = $_POST;
        $customerName = trim($_POST['customer_name'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $leadSource = $_POST['lead_source'] ?? '';
        $productService = trim($_POST['product_service'] ?? '');
        $priority = $_POST['priority'] ?? '';
        $assignedUserId = $_POST['assigned_user_id'] ?: null;
        $leadStatus = $_POST['lead_status'] ?? '';
        $remarks = trim($_POST['remarks'] ?? '');

        if (empty($customerName)) $errors[] = 'Customer name is required.';
        if (empty($mobile)) $errors[] = 'Mobile number is required.';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (empty($leadSource)) $errors[] = 'Lead source is required.';
        if (empty($productService)) $errors[] = 'Product/Service is required.';
        if (empty($priority)) $errors[] = 'Priority is required.';
        if (empty($leadStatus)) $errors[] = 'Lead status is required.';

        if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO leads (customer_name, company_name, mobile, email, lead_source, product_service, priority, assigned_user_id, lead_status, remarks, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$customerName, $companyName, $mobile, $email, $leadSource, $productService, $priority, $assignedUserId, $leadStatus, $remarks, $_SESSION['user_id']]);
            $leadId = $db->lastInsertId();

            createActivity($leadId, $_SESSION['user_id'], 'Lead Created', 'Lead created by ' . $_SESSION['user_name'] . '.');

            if ($assignedUserId) {
                $assignStmt = $db->prepare("SELECT name FROM users WHERE id = ?");
                $assignStmt->execute([$assignedUserId]);
                $assignUser = $assignStmt->fetch();
                createActivity($leadId, $_SESSION['user_id'], 'Lead Assigned', 'Lead assigned to ' . ($assignUser['name'] ?? 'Unknown') . '.');
            }

            setFlashMessage('success', 'Lead created successfully.');
            redirect('/LeadManagement/admin/leads/view.php?id=' . $leadId);
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
            <h4><i class="bi bi-plus-circle me-2"></i>Add New Lead</h4>
            <a href="/LeadManagement/admin/leads/index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Leads
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

        <div class="form-container">
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= escape($old['customer_name'] ?? '') ?>" placeholder="Enter customer name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?= escape($old['company_name'] ?? '') ?>" placeholder="Enter company name">
                    </div>
                    <div class="col-md-6">
                        <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mobile" name="mobile" value="<?= escape($old['mobile'] ?? '') ?>" placeholder="e.g. 9876543210" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= escape($old['email'] ?? '') ?>" placeholder="customer@example.com">
                    </div>
                    <div class="col-md-6">
                        <label for="lead_source" class="form-label">Lead Source <span class="text-danger">*</span></label>
                        <select class="form-select" id="lead_source" name="lead_source" required>
                            <option value="">Select Source</option>
                            <?php foreach (getLeadSources() as $source): ?>
                            <option value="<?= $source ?>" <?= ($old['lead_source'] ?? '') === $source ? 'selected' : '' ?>><?= $source ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="product_service" class="form-label">Product/Service <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="product_service" name="product_service" value="<?= escape($old['product_service'] ?? '') ?>" placeholder="e.g. CRM Software" required>
                    </div>
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="">Select Priority</option>
                            <?php foreach (getPriorities() as $p): ?>
                            <option value="<?= $p ?>" <?= ($old['priority'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="lead_status" class="form-label">Lead Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="lead_status" name="lead_status" required>
                            <option value="">Select Status</option>
                            <?php foreach (getLeadStatuses() as $s): ?>
                            <option value="<?= $s ?>" <?= ($old['lead_status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="assigned_user_id" class="form-label">Assign To</label>
                        <select class="form-select" id="assigned_user_id" name="assigned_user_id">
                            <option value="">Unassigned</option>
                            <?php foreach ($usersList as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($old['assigned_user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= escape($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Add any notes or remarks..."><?= escape($old['remarks'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Create Lead</button>
                        <a href="/LeadManagement/admin/leads/index.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
