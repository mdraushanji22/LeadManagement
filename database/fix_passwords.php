<?php
/**
 * Setup & Fix Script - Run once, then DELETE this file.
 * Access: http://localhost/LeadManagement/database/fix_passwords.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "<h2>Lead Management System - Setup & Fix</h2>";

try {
    // Create database if it doesn't exist
    $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS lead_management DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE lead_management");
    echo "<p style='color:green'>Database 'lead_management' ready.</p>";

    // Check if users table exists, if not import full SQL
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (empty($tables)) {
        echo "<p>Users table not found. Please import <code>lead_management.sql</code> via phpMyAdmin first.</p>";
        echo "<p>Steps: Open phpMyAdmin -> Import -> Select <code>database/lead_management.sql</code> -> Click Go</p>";
        exit;
    }

    // Fix passwords AND roles for all demo users
    $adminHash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $userHash  = password_hash('User@123', PASSWORD_DEFAULT);

    $updates = [
        ['admin@example.com', $adminHash, 'admin', 'Admin User'],
        ['user@example.com',  $userHash,  'user',  'Rahul Sharma'],
        ['priya@example.com', $userHash,  'user',  'Priya Patel'],
        ['amit@example.com',  $userHash,  'user',  'Amit Kumar'],
        ['neha@example.com',  $userHash,  'user',  'Neha Singh'],
    ];

    $stmt = $pdo->prepare("UPDATE users SET password = ?, role = ? WHERE email = ?");
    foreach ($updates as $u) {
        $stmt->execute([$u[1], $u[2], $u[0]]);
        $count = $stmt->rowCount();
        $status = $count > 0 ? "Updated" : "Already OK";
        echo "<p><strong>{$u[3]}</strong> ({$u[0]}) | Role: <code>{$u[2]}</code> | $status</p>";
    }

    // Show all users in database
    echo "<hr><h3>All Users in Database:</h3>";
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    $allUsers = $pdo->query("SELECT id, name, email, role, status FROM users ORDER BY id")->fetchAll();
    foreach ($allUsers as $u) {
        $roleColor = $u['role'] === 'admin' ? 'red' : 'blue';
        echo "<tr>";
        echo "<td>{$u['id']}</td>";
        echo "<td>{$u['name']}</td>";
        echo "<td>{$u['email']}</td>";
        echo "<td style='color:{$roleColor};font-weight:bold'>{$u['role']}</td>";
        echo "<td>{$u['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<br><hr>";
    echo "<h3>Login Credentials:</h3>";
    echo "<p><strong>Admin:</strong> admin@example.com / Admin@123</p>";
    echo "<p><strong>User:</strong> user@example.com / User@123</p>";
    echo "<br><p style='color:red'><strong>IMPORTANT: Delete this file after use!</strong></p>";
    echo "<p><a href='/LeadManagement/auth/login.php'>Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running in XAMPP.</p>";
}
