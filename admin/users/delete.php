<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
        redirect('/LeadManagement/admin/users/index.php');
    }

    $userId = intval($_POST['user_id'] ?? 0);
    if (!$userId) {
        setFlashMessage('error', 'Invalid user ID.');
        redirect('/LeadManagement/admin/users/index.php');
    }

    if ($userId == $_SESSION['user_id']) {
        setFlashMessage('error', 'You cannot delete your own account.');
        redirect('/LeadManagement/admin/users/index.php');
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        setFlashMessage('error', 'User not found.');
        redirect('/LeadManagement/admin/users/index.php');
    }

    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    setFlashMessage('success', 'User deleted successfully.');
} else {
    setFlashMessage('error', 'Invalid request method.');
}

redirect('/LeadManagement/admin/users/index.php');
