<?php
$pageTitle = 'My Leads';
require_once __DIR__ . '/../../includes/auth.php';
requireUser();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$source = $_GET['source'] ?? '';
$priority = $_GET['priority'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = intval($_GET['per_page'] ?? 10);
$perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

$where = ["l.deleted_at IS NULL", "l.assigned_user_id = ?"];
$params = [$userId];

if ($search) {
    $where[] = "(l.customer_name LIKE ? OR l.company_name LIKE ? OR l.mobile LIKE ? OR l.email LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}
if ($status) { $where[] = "l.lead_status = ?"; $params[] = $status; }
if ($source) { $where[] = "l.lead_source = ?"; $params[] = $source; }
if ($priority) { $where[] = "l.priority = ?"; $params[] = $priority; }
if ($dateFrom) { $where[] = "l.created_at >= ?"; $params[] = $dateFrom . ' 00:00:00'; }
if ($dateTo) { $where[] = "l.created_at <= ?"; $params[] = $dateTo . ' 23:59:59'; }

$whereClause = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM leads l WHERE $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("
    SELECT l.*,
           (SELECT MAX(f.next_follow_up_date) FROM follow_ups f WHERE f.lead_id = l.id AND f.status = 'Pending' AND f.next_follow_up_date IS NOT NULL) as next_followup
    FROM leads l
    WHERE $whereClause
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$perPage, $offset]);
$stmt->execute($allParams);
$leads = $stmt->fetchAll();

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
            <h4><i class="bi bi-people me-2"></i>My Leads</h4>
        </div>

        <!-- Filters -->
        <div class="filter-container">
            <form method="GET" action="">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search" placeholder="Name, company, mobile..." value="<?= escape($search) ?>">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Status</option>
                            <?php foreach (getLeadStatuses() as $s): ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small">Source</label>
                        <select class="form-select form-select-sm" name="source">
                            <option value="">All Sources</option>
                            <?php foreach (getLeadSources() as $s): ?>
                            <option value="<?= $s ?>" <?= $source === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small">Priority</label>
                        <select class="form-select form-select-sm" name="priority">
                            <option value="">All Priorities</option>
                            <?php foreach (getPriorities() as $p): ?>
                            <option value="<?= $p ?>" <?= $priority === $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 col-sm-6">
                        <label class="form-label small">From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="<?= escape($dateFrom) ?>">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small">To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="<?= escape($dateTo) ?>">
                    </div>
                </div>
                <div class="mt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Apply</button>
                    <a href="/LeadManagement/user/leads/index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
                </div>
            </form>
        </div>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">Showing <?= count($leads) ?> of <?= $totalRecords ?> leads</span>
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted">Per page:</label>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                        <?php foreach ([10, 25, 50, 100] as $pp): ?>
                        <option value="?<?= http_build_query(array_merge($_GET, ['per_page' => $pp, 'page' => 1])) ?>" <?= $perPage === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Source</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Next Follow-up</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leads)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No leads found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td>#<?= $lead['id'] ?></td>
                            <td><a href="/LeadManagement/user/leads/view.php?id=<?= $lead['id'] ?>" class="text-decoration-none fw-medium"><?= escape($lead['customer_name']) ?></a></td>
                            <td><?= escape($lead['company_name'] ?? '-') ?></td>
                            <td><?= escape($lead['mobile']) ?></td>
                            <td><?= escape($lead['email'] ?? '-') ?></td>
                            <td><span class="badge bg-light text-dark"><?= escape($lead['lead_source']) ?></span></td>
                            <td><?= getPriorityBadge($lead['priority']) ?></td>
                            <td><?= getLeadStatusBadge($lead['lead_status']) ?></td>
                            <td><?= $lead['next_followup'] ? formatDate($lead['next_followup']) : '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/LeadManagement/user/leads/view.php?id=<?= $lead['id'] ?>" class="btn btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="/LeadManagement/user/leads/edit.php?id=<?= $lead['id'] ?>" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                    </li>
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
