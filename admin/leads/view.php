<?php
$pageTitle = 'Lead Details';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDBConnection();
$leadId = intval($_GET['id'] ?? 0);

if (!$leadId) {
    setFlashMessage('error', 'Invalid lead ID.');
    redirect('/LeadManagement/admin/leads/index.php');
}

$stmt = $db->prepare("SELECT l.*, u.name as assigned_user_name, c.name as created_by_name FROM leads l LEFT JOIN users u ON l.assigned_user_id = u.id LEFT JOIN users c ON l.created_by = c.id WHERE l.id = ? AND l.deleted_at IS NULL");
$stmt->execute([$leadId]);
$lead = $stmt->fetch();

if (!$lead) {
    setFlashMessage('error', 'Lead not found.');
    redirect('/LeadManagement/admin/leads/index.php');
}

$followUpsStmt = $db->prepare("SELECT f.*, u.name as user_name FROM follow_ups f JOIN users u ON f.user_id = u.id WHERE f.lead_id = ? ORDER BY f.follow_up_date DESC, f.created_at DESC");
$followUpsStmt->execute([$leadId]);
$followUps = $followUpsStmt->fetchAll();

$activitiesStmt = $db->prepare("SELECT a.*, u.name as user_name FROM lead_activities a JOIN users u ON a.user_id = u.id WHERE a.lead_id = ? ORDER BY a.created_at DESC");
$activitiesStmt->execute([$leadId]);
$activities = $activitiesStmt->fetchAll();

$statusTimeline = ['New', 'Contacted', 'Follow Up', 'Interested', 'Converted'];
$isLost = in_array($lead['lead_status'], ['Lost', 'Not Interested']);

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
            <h4><i class="bi bi-person-lines-fill me-2"></i>Lead #<?= $leadId ?> - <?= escape($lead['customer_name']) ?></h4>
            <div class="d-flex gap-2">
                <a href="/LeadManagement/admin/leads/edit.php?id=<?= $leadId ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i> Edit</a>
                <a href="/LeadManagement/admin/leads/assign.php?id=<?= $leadId ?>" class="btn btn-info btn-sm"><i class="bi bi-person-plus me-1"></i> Assign</a>
                <a href="/LeadManagement/admin/leads/index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
            </div>
        </div>

        <!-- Status Timeline -->
        <div class="table-container mb-4">
            <h6 class="mb-3 fw-bold">Lead Status Timeline</h6>
            <div class="status-timeline">
                <?php if ($isLost): ?>
                    <?php foreach ($statusTimeline as $step): ?>
                    <span class="status-step"><?= $step ?></span>
                    <span class="status-arrow"><i class="bi bi-arrow-right"></i></span>
                    <?php endforeach; ?>
                    <span class="status-step lost"><?= escape($lead['lead_status']) ?></span>
                <?php else: ?>
                    <?php foreach ($statusTimeline as $step): ?>
                    <span class="status-step <?= $step === $lead['lead_status'] ? 'active' : (array_search($step, $statusTimeline) < array_search($lead['lead_status'], $statusTimeline) ? 'active' : '') ?>"><?= $step ?></span>
                    <?php if ($step !== 'Converted'): ?>
                    <span class="status-arrow"><i class="bi bi-arrow-right"></i></span>
                    <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <!-- Lead Information -->
            <div class="col-lg-6">
                <div class="table-container">
                    <h6 class="mb-3 fw-bold">Lead Information</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tr><td class="text-muted" style="width:40%">Customer Name</td><td class="fw-medium"><?= escape($lead['customer_name']) ?></td></tr>
                            <tr><td class="text-muted">Company</td><td><?= escape($lead['company_name'] ?? '-') ?></td></tr>
                            <tr><td class="text-muted">Mobile</td><td><?= escape($lead['mobile']) ?></td></tr>
                            <tr><td class="text-muted">Email</td><td><?= escape($lead['email'] ?? '-') ?></td></tr>
                            <tr><td class="text-muted">Lead Source</td><td><span class="badge bg-light text-dark"><?= escape($lead['lead_source']) ?></span></td></tr>
                            <tr><td class="text-muted">Product/Service</td><td><?= escape($lead['product_service']) ?></td></tr>
                            <tr><td class="text-muted">Priority</td><td><?= getPriorityBadge($lead['priority']) ?></td></tr>
                            <tr><td class="text-muted">Status</td><td><?= getLeadStatusBadge($lead['lead_status']) ?></td></tr>
                            <tr><td class="text-muted">Assigned To</td><td><?= escape($lead['assigned_user_name'] ?? 'Unassigned') ?></td></tr>
                            <tr><td class="text-muted">Created By</td><td><?= escape($lead['created_by_name'] ?? '-') ?></td></tr>
                            <tr><td class="text-muted">Created Date</td><td><?= formatDateTime($lead['created_at']) ?></td></tr>
                            <tr><td class="text-muted">Updated Date</td><td><?= formatDateTime($lead['updated_at']) ?></td></tr>
                            <?php if ($lead['remarks']): ?>
                            <tr><td class="text-muted">Remarks</td><td><?= nl2br(escape($lead['remarks'])) ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Follow-ups -->
            <div class="col-lg-6">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">Follow-up History</h6>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFollowupModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Follow-up
                        </button>
                    </div>
                    <?php if (empty($followUps)): ?>
                    <p class="text-muted text-center py-3">No follow-ups yet.</p>
                    <?php else: ?>
                    <?php foreach ($followUps as $fu): ?>
                    <div class="followup-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-<?= $fu['follow_up_type'] === 'Call' ? 'primary' : ($fu['follow_up_type'] === 'Email' ? 'info' : ($fu['follow_up_type'] === 'WhatsApp' ? 'success' : 'secondary')) ?>">
                                    <i class="bi <?= getFollowUpTypeIcon($fu['follow_up_type']) ?> me-1"></i>
                                    <?= escape($fu['follow_up_type']) ?>
                                </span>
                                <?= getStatusBadge($fu['status']) ?>
                            </div>
                            <small class="text-muted"><?= formatDate($fu['follow_up_date']) ?></small>
                        </div>
                        <?php if ($fu['discussion']): ?>
                        <p class="mt-2 mb-1 small"><?= nl2br(escape($fu['discussion'])) ?></p>
                        <?php endif; ?>
                        <?php if ($fu['next_follow_up_date']): ?>
                        <small class="text-muted"><i class="bi bi-calendar me-1"></i>Next: <?= formatDate($fu['next_follow_up_date']) ?></small>
                        <?php endif; ?>
                        <div class="mt-2">
                            <small class="text-muted">By <?= escape($fu['user_name']) ?></small>
                            <?php if ($fu['status'] === 'Pending'): ?>
                            <div class="btn-group btn-group-sm ms-2">
                                <form method="POST" action="/LeadManagement/ajax/followups.php" class="d-inline">
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="followup_id" value="<?= $fu['id'] ?>">
                                    <input type="hidden" name="lead_id" value="<?= $leadId ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Complete"><i class="bi bi-check"></i></button>
                                </form>
                                <form method="POST" action="/LeadManagement/ajax/followups.php" class="d-inline">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="followup_id" value="<?= $fu['id'] ?>">
                                    <input type="hidden" name="lead_id" value="<?= $leadId ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel"><i class="bi bi-x"></i></button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="table-container mt-4">
            <h6 class="mb-3 fw-bold">Activity Timeline</h6>
            <div class="timeline">
                <?php foreach ($activities as $act): ?>
                <div class="timeline-item <?= $act['activity_type'] === 'Lead Converted' ? 'success' : ($act['activity_type'] === 'Lead Lost' ? 'danger' : ($act['activity_type'] === 'Follow-up Completed' ? 'success' : '')) ?>">
                    <div class="timeline-date"><?= formatDateTime($act['created_at']) ?></div>
                    <div class="timeline-content">
                        <i class="bi <?= getActivityIcon($act['activity_type']) ?> me-1"></i>
                        <strong><?= escape($act['activity_type']) ?></strong>
                        <span class="text-muted ms-1">- <?= escape($act['description']) ?></span>
                        <br><small class="text-muted">by <?= escape($act['user_name']) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Follow-up Modal -->
<div class="modal fade" id="addFollowupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/LeadManagement/ajax/followups.php">
                <div class="modal-header">
                    <h5 class="modal-title">Add Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="lead_id" value="<?= $leadId ?>">
                    <div class="mb-3">
                        <label class="form-label">Follow-up Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="follow_up_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Follow-up Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="follow_up_type" required>
                            <option value="">Select Type</option>
                            <?php foreach (getFollowUpTypes() as $type): ?>
                            <option value="<?= $type ?>"><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discussion</label>
                        <textarea class="form-control" name="discussion" rows="3" placeholder="Enter discussion details..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Follow-up Date</label>
                        <input type="date" class="form-control" name="next_follow_up_date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <?php foreach (getFollowUpStatuses() as $s): ?>
                            <option value="<?= $s ?>" <?= $s === 'Pending' ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Follow-up</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
