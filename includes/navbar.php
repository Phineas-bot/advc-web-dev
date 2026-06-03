<?php
declare(strict_types=1);

$departments = fetch_departments();
$user = current_user();
?>
<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3 fw-bold text-dark" href="<?= e(base_url('index.php')) ?>">
            <?php if (is_file(__DIR__ . '/../assets/images/logo-small.svg')): ?>
                <img src="<?= e(base_url('assets/images/logo-small.svg')) ?>" alt="CIMEN" width="40" height="40" class="d-inline-block align-text-top">
                <div>
                    <div class="fw-bold">CIMEN HRMS</div>
                    <small class="d-block text-secondary fw-normal">Cement and Construction Company</small>
                </div>
            <?php else: ?>
                <span class="brand-mark">C</span>
                <span>
                    CIMEN HRMS
                    <small class="d-block text-secondary fw-normal">Cement and Construction Company</small>
                </span>
            <?php endif; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= e(base_url('index.php')) ?>">Home</a></li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(base_url('dashboard.php')) ?>">Dashboard</a></li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Departments</a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <?php foreach ($departments as $department): ?>
                                    <li><a class="dropdown-item" href="<?= e(base_url('department.php?id=' . (int) $department['id'])) ?>"><?= e($department['department_name']) ?></a></li>
                                <?php endforeach; ?>
                                <?php if (is_super_admin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= e(base_url('modules/departments/index.php')) ?>">Manage Departments</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if (is_super_admin()): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('modules/admins/index.php')) ?>">Admins</a></li>
                    <?php endif; ?>
                    <?php if (is_admin()): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('modules/employees/index.php')) ?>">Employees</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(base_url('profile.php')) ?>">Profile</a></li>
                    <li class="nav-item"><a class="btn btn-warning btn-sm ms-lg-2" href="<?= e(base_url('logout.php')) ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(base_url('login.php')) ?>">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(base_url('register.php')) ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
