<?php
require_once __DIR__ . '/../includes/auth.php';

setFlashMessage('success', 'You have been logged out successfully.');
logoutUser();
redirect('/LeadManagement/auth/login.php');
