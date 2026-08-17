<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$search = trim($_GET['search'] ?? '');
$limit = min(50, max(1, intval($_GET['limit'] ?? 10)));
$userId = $_SESSION['user_id'];
$isAdmin = isAdmin();

$db = getDBConnection();

$where = ["l.deleted_at IS NULL"];
$params = [];

if (!$isAdmin) {
    $where[] = "l.assigned_user_id = ?";
    $params[] = $userId;
}

if ($search) {
    $where[] = "(l.customer_name LIKE ? OR l.company_name LIKE ? OR l.mobile LIKE ? OR l.email LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

$whereClause = implode(' AND ', $where);
$limitParam = intval($limit);
$params[] = $limitParam;

$stmt = $db->prepare("
    SELECT l.id, l.customer_name, l.company_name, l.mobile, l.email, l.lead_status
    FROM leads l
    WHERE $whereClause
    ORDER BY l.customer_name ASC
    LIMIT ?
");
$stmt->execute($params);
$results = $stmt->fetchAll();

echo json_encode($results);
