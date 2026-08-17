<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$isAdmin = isAdmin();

$stats = [
    'total_leads' => getTotalLeadsCount($isAdmin ? null : $userId),
    'new_leads' => getLeadsCountByStatus('New', $isAdmin ? null : $userId),
    'interested_leads' => getLeadsCountByStatus('Interested', $isAdmin ? null : $userId),
    'converted_leads' => getLeadsCountByStatus('Converted', $isAdmin ? null : $userId),
    'lost_leads' => getLeadsCountByStatus('Lost', $isAdmin ? null : $userId),
    'today_followups' => getTodayFollowUpsCount($isAdmin ? null : $userId),
    'overdue_followups' => getOverdueFollowUpsCount($isAdmin ? null : $userId),
];

echo json_encode($stats);
