<?php
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('/LeadManagement/admin/dashboard.php');
    } else {
        redirect('/LeadManagement/user/dashboard.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if (empty($name)) {
            $error = 'Name is required.';
        } elseif (empty($email)) {
            $error = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (empty($password)) {
            $error = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['admin', 'user'])) {
            $error = 'Invalid role selected.';
        } else {
            $db = getDBConnection();
            $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password, phone, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())");
                $stmt->execute([$name, $email, $hashedPassword, $phone, $role]);

                setFlashMessage('success', 'Account created successfully. Please login.');
                redirect('/LeadManagement/auth/login.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Lead Management System</title>
    <script>
        (function() {
            var theme = localStorage.getItem('lms-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/LeadManagement/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-sm btn-outline-secondary" id="themeToggle" title="Toggle Dark Mode" style="border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;padding:0;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                    <i class="bi bi-graph-up-arrow text-primary" style="font-size: 2.5rem;"></i>
                    <h4 class="mt-2 fw-bold">Lead Management System</h4>
                    <p class="text-muted">Create your account</p>
                </div>

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <?= escape($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="name" name="name" value="<?= escape($_POST['name'] ?? '') ?>" placeholder="Enter full name" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?= escape($_POST['email'] ?? '') ?>" placeholder="you@example.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= escape($_POST['phone'] ?? '') ?>" placeholder="e.g. 9876543210">
                        </div>
                    </div>
                    <input type="hidden" name="role" value="user">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Min 6 characters" required minlength="6">
                        </div>
                        <small class="text-muted">Minimum 6 characters.</small>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-person-plus me-1"></i> Sign Up
                    </button>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">Already have an account? <a href="/LeadManagement/auth/login.php" class="text-decoration-none">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function() {
        var ti = document.getElementById('themeIcon');
        var tb = document.getElementById('themeToggle');
        function apply(t) {
            document.documentElement.setAttribute('data-bs-theme', t);
            localStorage.setItem('lms-theme', t);
            if (ti) { ti.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill'; }
        }
        apply(localStorage.getItem('lms-theme') || 'light');
        if (tb) tb.addEventListener('click', function() {
            apply(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
        });
    })();
    </script>
</body>
</html>
