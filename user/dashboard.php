<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/auth.php';
requireUser();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

$totalLeads = getTotalLeadsCount($userId);
$newLeads = getLeadsCountByStatus('New', $userId);
$contactedLeads = getLeadsCountByStatus('Contacted', $userId);
$interestedLeads = getLeadsCountByStatus('Interested', $userId);
$convertedLeads = getLeadsCountByStatus('Converted', $userId);
$lostLeads = getLeadsCountByStatus('Lost', $userId);
$todayFollowUps = getTodayFollowUpsCount($userId);
$overdueFollowUps = getOverdueFollowUpsCount($userId);

$stmt = $db->prepare("SELECT l.*, u.name as assigned_user_name FROM leads l LEFT JOIN users u ON l.assigned_user_id = u.id WHERE l.assigned_user_id = ? AND l.deleted_at IS NULL ORDER BY l.created_at DESC LIMIT 10");
$stmt->execute([$userId]);
$recentLeads = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT f.*, l.customer_name, l.company_name
    FROM follow_ups f
    JOIN leads l ON f.lead_id = l.id
    WHERE f.user_id = ? AND f.follow_up_date = CURDATE() AND f.status = 'Pending' AND l.deleted_at IS NULL
    ORDER BY f.follow_up_date ASC
    LIMIT 10
");
$stmt->execute([$userId]);
$todaysFollowUps = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="content-wrapper">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="content">
        <?php $flash = getFlashMessage(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= escape($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <h4><i class="bi bi-speedometer2 me-2"></i>My Dashboard</h4>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $totalLeads ?></div>
                            <div class="stat-label">My Leads</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $newLeads ?></div>
                            <div class="stat-label">New</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                            <i class="bi bi-hand-thumbs-up"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $interestedLeads ?></div>
                            <div class="stat-label">Interested</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $convertedLeads ?></div>
                            <div class="stat-label">Converted</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                            <i class="bi bi-x-octagon"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $lostLeads ?></div>
                            <div class="stat-label">Lost</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $todayFollowUps ?></div>
                            <div class="stat-label">Today's Follow-ups</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $overdueFollowUps ?></div>
                            <div class="stat-label">Overdue</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Leads -->
            <div class="col-lg-8">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">My Recent Leads</h6>
                        <a href="/LeadManagement/user/leads/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentLeads)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No leads assigned to you.</td></tr>
                                <?php else: ?>
                                <?php foreach ($recentLeads as $lead): ?>
                                <tr>
                                    <td>#<?= $lead['id'] ?></td>
                                    <td>
                                        <a href="/LeadManagement/user/leads/view.php?id=<?= $lead['id'] ?>" class="text-decoration-none fw-medium">
                                            <?= escape($lead['customer_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= escape($lead['company_name'] ?? '-') ?></td>
                                    <td><?= getLeadStatusBadge($lead['lead_status']) ?></td>
                                    <td><?= getPriorityBadge($lead['priority']) ?></td>
                                    <td><?= formatDate($lead['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Today's Follow-ups -->
            <div class="col-lg-4">
                <div class="table-container">
                    <h6 class="mb-3 fw-bold">Today's Follow-ups</h6>
                    <?php if (empty($todaysFollowUps)): ?>
                    <p class="text-muted text-center py-3">No follow-ups for today.</p>
                    <?php else: ?>
                    <?php foreach ($todaysFollowUps as $fu): ?>
                    <div class="followup-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= escape($fu['customer_name']) ?></strong><br>
                                <small class="text-muted"><?= escape($fu['company_name'] ?? '') ?></small>
                            </div>
                            <span class="badge bg-info">
                                <i class="bi <?= getFollowUpTypeIcon($fu['follow_up_type']) ?> me-1"></i>
                                <?= escape($fu['follow_up_type']) ?>
                            </span>
                        </div>
                        <div class="mt-2">
                            <a href="/LeadManagement/user/leads/view.php?id=<?= $fu['lead_id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
