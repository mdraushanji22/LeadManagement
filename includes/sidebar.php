<nav id="sidebar" class="sidebar bg-dark">
    <div class="sidebar-header p-3">
        <h5 class="text-white mb-0">
            <i class="bi bi-graph-up-arrow me-2"></i>LMS
        </h5>
    </div>
    <ul class="nav flex-column p-2">
        <?php if (isAdmin()): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' && strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>" href="/LeadManagement/admin/dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= strpos($_SERVER['PHP_SELF'], '/admin/leads/') !== false ? 'active' : '' ?>" href="/LeadManagement/admin/leads/index.php">
                <i class="bi bi-people me-2"></i> Leads
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= strpos($_SERVER['PHP_SELF'], '/admin/users/') !== false ? 'active' : '' ?>" href="/LeadManagement/admin/users/index.php">
                <i class="bi bi-person-gear me-2"></i> Users
            </a>
        </li>
        <?php else: ?>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' && strpos($_SERVER['PHP_SELF'], '/user/') !== false ? 'active' : '' ?>" href="/LeadManagement/user/dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= strpos($_SERVER['PHP_SELF'], '/user/leads/index.php') !== false ? 'active' : '' ?>" href="/LeadManagement/user/leads/index.php">
                <i class="bi bi-people me-2"></i> My Leads
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= strpos($_SERVER['PHP_SELF'], '/user/leads/followup.php') !== false ? 'active' : '' ?>" href="/LeadManagement/user/leads/followup.php">
                <i class="bi bi-calendar-check me-2"></i> Follow-ups
            </a>
        </li>
        <?php endif; ?>
        <li class="nav-item mt-3">
            <hr class="text-white-50">
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="/LeadManagement/auth/logout.php">
                <i class="bi bi-box-arrow-left me-2"></i> Logout
            </a>
        </li>
    </ul>
</nav>
