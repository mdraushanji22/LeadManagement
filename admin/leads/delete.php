<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
        redirect('/LeadManagement/admin/leads/index.php');
    }

    $leadId = intval($_POST['lead_id'] ?? 0);
    if (!$leadId) {
        setFlashMessage('error', 'Invalid lead ID.');
        redirect('/LeadManagement/admin/leads/index.php');
    }

    $stmt = $db->prepare("SELECT id FROM leads WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch();

    if (!$lead) {
        setFlashMessage('error', 'Lead not found.');
        redirect('/LeadManagement/admin/leads/index.php');
    }

    $stmt = $db->prepare("UPDATE leads SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$leadId]);

    setFlashMessage('success', 'Lead deleted successfully.');
} else {
    setFlashMessage('error', 'Invalid request method.');
}

redirect('/LeadManagement/admin/leads/index.php');
