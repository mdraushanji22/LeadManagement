<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('/LeadManagement/admin/dashboard.php');
    } else {
        redirect('/LeadManagement/user/dashboard.php');
    }
} else {
    redirect('/LeadManagement/auth/login.php');
}
