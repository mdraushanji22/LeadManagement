<?php
$pageTitle = 'Edit Lead';
require_once __DIR__ . '/../../includes/auth.php';
requireUser();

$db = getDBConnection();
$leadId = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$leadId) {
    setFlashMessage('error', 'Invalid lead ID.');
    redirect('/LeadManagement/user/leads/index.php');
}

$stmt = $db->prepare("SELECT * FROM leads WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$leadId]);
$lead = $stmt->fetch();

if (!$lead) {
    setFlashMessage('error', 'Lead not found.');
    redirect('/LeadManagement/user/leads/index.php');
}

if (!isAdmin() && $lead['assigned_user_id'] != $userId) {
    setFlashMessage('error', 'You are not authorized to edit this lead.');
    redirect('/LeadManagement/user/leads/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $customerName = trim($_POST['customer_name'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $leadSource = $_POST['lead_source'] ?? '';
        $productService = trim($_POST['product_service'] ?? '');
        $priority = $_POST['priority'] ?? '';
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
            $activities = [];
            if ($lead['lead_status'] !== $leadStatus) {
                $activities[] = ['Status Changed', 'Status changed from ' . $lead['lead_status'] . ' to ' . $leadStatus . '.'];
            }
            if ($lead['priority'] !== $priority) {
                $activities[] = ['Priority Changed', 'Priority changed from ' . $lead['priority'] . ' to ' . $priority . '.'];
            }
            if ($lead['remarks'] !== $remarks && $remarks) {
                $activities[] = ['Remark Added', 'Remarks updated.'];
            }
            if (empty($activities)) {
                $activities[] = ['Lead Updated', 'Lead information updated.'];
            }

            $stmt = $db->prepare("UPDATE leads SET customer_name=?, company_name=?, mobile=?, email=?, lead_source=?, product_service=?, priority=?, lead_status=?, remarks=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$customerName, $companyName, $mobile, $email, $leadSource, $productService, $priority, $leadStatus, $remarks, $leadId]);

            foreach ($activities as $act) {
                createActivity($leadId, $userId, $act[0], $act[1]);
            }

            setFlashMessage('success', 'Lead updated successfully.');
            redirect('/LeadManagement/user/leads/view.php?id=' . $leadId);
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
            <h4><i class="bi bi-pencil-square me-2"></i>Edit Lead #<?= $leadId ?></h4>
            <a href="/LeadManagement/user/leads/view.php?id=<?= $leadId ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Lead
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
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= escape($lead['customer_name']) ?>" placeholder="Enter customer name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?= escape($lead['company_name'] ?? '') ?>" placeholder="Enter company name">
                    </div>
                    <div class="col-md-6">
                        <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mobile" name="mobile" value="<?= escape($lead['mobile']) ?>" placeholder="e.g. 9876543210" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= escape($lead['email'] ?? '') ?>" placeholder="customer@example.com">
                    </div>
                    <div class="col-md-6">
                        <label for="lead_source" class="form-label">Lead Source <span class="text-danger">*</span></label>
                        <select class="form-select" id="lead_source" name="lead_source" required>
                            <option value="">Select Source</option>
                            <?php foreach (getLeadSources() as $source): ?>
                            <option value="<?= $source ?>" <?= $lead['lead_source'] === $source ? 'selected' : '' ?>><?= $source ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="product_service" class="form-label">Product/Service <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="product_service" name="product_service" value="<?= escape($lead['product_service']) ?>" placeholder="e.g. CRM Software" required>
                    </div>
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="">Select Priority</option>
                            <?php foreach (getPriorities() as $p): ?>
                            <option value="<?= $p ?>" <?= $lead['priority'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="lead_status" class="form-label">Lead Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="lead_status" name="lead_status" required>
                            <option value="">Select Status</option>
                            <?php foreach (getLeadStatuses() as $s): ?>
                            <option value="<?= $s ?>" <?= $lead['lead_status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Add any notes or remarks..."><?= escape($lead['remarks'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update Lead</button>
                        <a href="/LeadManagement/user/leads/view.php?id=<?= $leadId ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
