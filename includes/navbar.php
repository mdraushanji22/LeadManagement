<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3" id="mainNavbar">
    <button class="btn btn-sm btn-outline-secondary me-3 d-md-none" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    <div class="ms-auto d-flex align-items-center">
        <button class="btn btn-sm btn-outline-secondary me-3" id="themeToggle" title="Toggle Dark Mode">
            <i class="bi bi-moon-fill" id="themeIcon"></i>
        </button>
        <span class="me-3 d-none d-sm-inline">
            <i class="bi bi-person-circle me-1"></i>
            <?= escape($_SESSION['user_name'] ?? 'User') ?>
            <span class="badge bg-secondary ms-1"><?= ucfirst(escape($_SESSION['user_role'] ?? 'user')) ?></span>
        </span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-gear"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="/LeadManagement/auth/logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
