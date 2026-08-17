<?php
/**
 * Common Helper Functions
 */

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function escape($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

function getLeadStatusBadge($status) {
    $badges = [
        'New'            => 'primary',
        'Contacted'      => 'info',
        'Follow Up'      => 'warning',
        'Interested'     => 'secondary',
        'Converted'      => 'success',
        'Not Interested' => 'dark',
        'Lost'           => 'danger',
    ];
    $class = $badges[$status] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . escape($status) . '</span>';
}

function getPriorityBadge($priority) {
    $badges = [
        'Low'    => 'success',
        'Medium' => 'info',
        'High'   => 'warning',
        'Urgent' => 'danger',
    ];
    $class = $badges[$priority] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . escape($priority) . '</span>';
}

function getStatusBadge($status) {
    $badges = [
        'Pending'   => 'warning',
        'Completed' => 'success',
        'Cancelled' => 'danger',
    ];
    $class = $badges[$status] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . escape($status) . '</span>';
}

function getFollowUpTypeIcon($type) {
    $icons = [
        'Call'      => 'bi-telephone',
        'Email'     => 'bi-envelope',
        'WhatsApp'  => 'bi-whatsapp',
        'Meeting'   => 'bi-people',
    ];
    return $icons[$type] ?? 'bi-chat';
}

function getActivityIcon($type) {
    $icons = [
        'Lead Created'         => 'bi-plus-circle text-primary',
        'Lead Updated'         => 'bi-pencil-square text-info',
        'Lead Assigned'        => 'bi-person-plus text-warning',
        'Status Changed'       => 'bi-arrow-repeat text-info',
        'Priority Changed'     => 'bi-flag text-warning',
        'Follow-up Added'      => 'bi-calendar-plus text-primary',
        'Follow-up Completed'  => 'bi-check-circle text-success',
        'Follow-up Rescheduled'=> 'bi-calendar-event text-warning',
        'Follow-up Cancelled'  => 'bi-x-circle text-danger',
        'Remark Added'         => 'bi-chat-dots text-secondary',
        'Lead Converted'       => 'bi-trophy text-success',
        'Lead Lost'            => 'bi-x-octagon text-danger',
    ];
    return $icons[$type] ?? 'bi-activity text-secondary';
}

function createActivity($leadId, $userId, $activityType, $description) {
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO lead_activities (lead_id, user_id, activity_type, description, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$leadId, $userId, $activityType, $description]);
}

function formatDate($date) {
    if (empty($date)) return '-';
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    return date('d M Y h:i A', strtotime($datetime));
}

function formatDateInput($date) {
    if (empty($date)) return '';
    return date('Y-m-d', strtotime($date));
}

function getTotalLeadsCount($userId = null) {
    $db = getDBConnection();
    $sql = "SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL";
    $params = [];
    if ($userId) {
        $sql .= " AND assigned_user_id = ?";
        $params[] = $userId;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getLeadsCountByStatus($status, $userId = null) {
    $db = getDBConnection();
    $sql = "SELECT COUNT(*) FROM leads WHERE lead_status = ? AND deleted_at IS NULL";
    $params = [$status];
    if ($userId) {
        $sql .= " AND assigned_user_id = ?";
        $params[] = $userId;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getTodayFollowUpsCount($userId = null) {
    $db = getDBConnection();
    $sql = "SELECT COUNT(*) FROM follow_ups f JOIN leads l ON f.lead_id = l.id WHERE f.follow_up_date = CURDATE() AND f.status = 'Pending' AND l.deleted_at IS NULL";
    $params = [];
    if ($userId) {
        $sql .= " AND f.user_id = ?";
        $params[] = $userId;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getOverdueFollowUpsCount($userId = null) {
    $db = getDBConnection();
    $sql = "SELECT COUNT(*) FROM follow_ups f JOIN leads l ON f.lead_id = l.id WHERE f.follow_up_date < CURDATE() AND f.status = 'Pending' AND l.deleted_at IS NULL";
    $params = [];
    if ($userId) {
        $sql .= " AND f.user_id = ?";
        $params[] = $userId;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getLeadSources() {
    return ['Website', 'Referral', 'Facebook', 'Instagram', 'Google', 'LinkedIn', 'Advertisement', 'Cold Call', 'Other'];
}

function getLeadStatuses() {
    return ['New', 'Contacted', 'Follow Up', 'Interested', 'Converted', 'Not Interested', 'Lost'];
}

function getPriorities() {
    return ['Low', 'Medium', 'High', 'Urgent'];
}

function getFollowUpTypes() {
    return ['Call', 'Email', 'WhatsApp', 'Meeting'];
}

function getFollowUpStatuses() {
    return ['Pending', 'Completed', 'Cancelled'];
}
