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
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } elseif (loginUser($email, $password)) {
            setFlashMessage('success', 'Welcome back, ' . $_SESSION['user_name'] . '!');
            if (isAdmin()) {
                redirect('/LeadManagement/admin/dashboard.php');
            } else {
                redirect('/LeadManagement/user/dashboard.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lead Management System</title>
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
                    <p class="text-muted">Sign in to your account</p>
                </div>

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <?= escape($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php $flash = getFlashMessage(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= escape($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?= escape($_POST['email'] ?? '') ?>" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </button>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">Don't have an account? <a href="/LeadManagement/auth/signup.php" class="text-decoration-none">Sign Up</a></p>
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
