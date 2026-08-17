<?php
/**
 * Authentication & Authorization Helpers
 */

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to access this page.');
        redirect('/LeadManagement/auth/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        setFlashMessage('error', 'You are not authorized to access this page.');
        redirect('/LeadManagement/user/dashboard.php');
        exit;
    }
}

function requireUser() {
    requireLogin();
}

function currentUser() {
    if (!isLoggedIn()) return null;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, name, email, phone, role, status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

function loginUser($email, $password) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
