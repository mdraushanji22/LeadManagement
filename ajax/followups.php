<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method.');
    header("Location: " . $_SERVER['HTTP_REFERER'] ?? '/');
    exit;
}

$action = $_POST['action'] ?? '';
$leadId = intval($_POST['lead_id'] ?? 0);
$followupId = intval($_POST['followup_id'] ?? 0);

if (!$leadId) {
    setFlashMessage('error', 'Invalid lead ID.');
    header("Location: " . $_SERVER['HTTP_REFERER'] ?? '/');
    exit;
}

$redirectUrl = isAdmin() ? "/LeadManagement/admin/leads/view.php?id=$leadId" : "/LeadManagement/user/leads/view.php?id=$leadId";

if ($action === 'add') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
        header("Location: $redirectUrl");
        exit;
    }

    $followUpDate = $_POST['follow_up_date'] ?? '';
    $followUpType = $_POST['follow_up_type'] ?? '';
    $discussion = trim($_POST['discussion'] ?? '');
    $nextFollowUpDate = $_POST['next_follow_up_date'] ?: null;
    $status = $_POST['status'] ?? 'Pending';

    if (empty($followUpDate) || empty($followUpType)) {
        setFlashMessage('error', 'Follow-up date and type are required.');
        header("Location: $redirectUrl");
        exit;
    }

    if (!in_array($followUpType, getFollowUpTypes())) {
        setFlashMessage('error', 'Invalid follow-up type.');
        header("Location: $redirectUrl");
        exit;
    }

    if (!in_array($status, getFollowUpStatuses())) {
        $status = 'Pending';
    }

    $stmt = $db->prepare("INSERT INTO follow_ups (lead_id, user_id, follow_up_date, follow_up_type, discussion, next_follow_up_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$leadId, $userId, $followUpDate, $followUpType, $discussion, $nextFollowUpDate, $status]);

    $dateFormatted = formatDate($followUpDate);
    createActivity($leadId, $userId, 'Follow-up Added', "Follow-up scheduled for $dateFormatted ($followUpType).");

    if ($status === 'Completed') {
        createActivity($leadId, $userId, 'Follow-up Completed', "Follow-up completed - " . ($discussion ? substr($discussion, 0, 100) : 'No details') . ".");
    }

    setFlashMessage('success', 'Follow-up added successfully.');
} elseif ($action === 'complete') {
    if (!$followupId) {
        setFlashMessage('error', 'Invalid follow-up ID.');
        header("Location: $redirectUrl");
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM follow_ups WHERE id = ? AND lead_id = ?");
    $stmt->execute([$followupId, $leadId]);
    $fu = $stmt->fetch();

    if (!$fu) {
        setFlashMessage('error', 'Follow-up not found.');
        header("Location: $redirectUrl");
        exit;
    }

    $stmt = $db->prepare("UPDATE follow_ups SET status = 'Completed', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$followupId]);

    createActivity($leadId, $userId, 'Follow-up Completed', "Follow-up (" . $fu['follow_up_type'] . " on " . formatDate($fu['follow_up_date']) . ") marked as completed.");

    setFlashMessage('success', 'Follow-up completed successfully.');
} elseif ($action === 'cancel') {
    if (!$followupId) {
        setFlashMessage('error', 'Invalid follow-up ID.');
        header("Location: $redirectUrl");
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM follow_ups WHERE id = ? AND lead_id = ?");
    $stmt->execute([$followupId, $leadId]);
    $fu = $stmt->fetch();

    if (!$fu) {
        setFlashMessage('error', 'Follow-up not found.');
        header("Location: $redirectUrl");
        exit;
    }

    $stmt = $db->prepare("UPDATE follow_ups SET status = 'Cancelled', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$followupId]);

    createActivity($leadId, $userId, 'Follow-up Cancelled', "Follow-up (" . $fu['follow_up_type'] . " on " . formatDate($fu['follow_up_date']) . ") was cancelled.");

    setFlashMessage('success', 'Follow-up cancelled.');
} else {
    setFlashMessage('error', 'Invalid action.');
}

header("Location: $redirectUrl");
exit;
