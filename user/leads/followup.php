<?php
$pageTitle = 'Follow-up';
require_once __DIR__ . '/../../includes/auth.php';
requireUser();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = ["f.user_id = ?", "l.deleted_at IS NULL"];
$params = [$userId];

if ($search) {
    $where[] = "(l.customer_name LIKE ? OR l.company_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam]);
}
if ($status) { $where[] = "f.status = ?"; $params[] = $status; }
if ($type) { $where[] = "f.follow_up_type = ?"; $params[] = $type; }

$whereClause = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM follow_ups f JOIN leads l ON f.lead_id = l.id WHERE $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("
    SELECT f.*, l.customer_name, l.company_name, l.id as lead_id
    FROM follow_ups f
    JOIN leads l ON f.lead_id = l.id
    WHERE $whereClause
    ORDER BY f.follow_up_date DESC, f.created_at DESC
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$perPage, $offset]);
$stmt->execute($allParams);
$followUps = $stmt->fetchAll();

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
            <h4><i class="bi bi-calendar-check me-2"></i>My Follow-ups</h4>
        </div>

        <div class="filter-container">
            <form method="GET" action="">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search" placeholder="Customer or company..." value="<?= escape($search) ?>">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Status</option>
                            <?php foreach (getFollowUpStatuses() as $s): ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small">Type</label>
                        <select class="form-select form-select-sm" name="type">
                            <option value="">All Types</option>
                            <?php foreach (getFollowUpTypes() as $t): ?>
                            <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Apply</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Company</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Next Follow-up</th>
                            <th>Status</th>
                            <th>Discussion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($followUps)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No follow-ups found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($followUps as $fu): ?>
                        <tr>
                            <td><a href="/LeadManagement/user/leads/view.php?id=<?= $fu['lead_id'] ?>" class="text-decoration-none fw-medium"><?= escape($fu['customer_name']) ?></a></td>
                            <td><?= escape($fu['company_name'] ?? '-') ?></td>
                            <td><span class="badge bg-info"><i class="bi <?= getFollowUpTypeIcon($fu['follow_up_type']) ?> me-1"></i><?= escape($fu['follow_up_type']) ?></span></td>
                            <td><?= formatDate($fu['follow_up_date']) ?></td>
                            <td><?= $fu['next_follow_up_date'] ? formatDate($fu['next_follow_up_date']) : '-' ?></td>
                            <td><?= getStatusBadge($fu['status']) ?></td>
                            <td><?= escape(substr($fu['discussion'] ?? '', 0, 50)) ?><?= strlen($fu['discussion'] ?? '') > 50 ? '...' : '' ?></td>
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
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
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
