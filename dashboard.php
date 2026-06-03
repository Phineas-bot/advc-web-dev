<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$user = current_user();
$role = $_SESSION['role'] ?? 'employee';
$pageTitle = 'Dashboard | CIMEN HRMS';

require_once __DIR__ . '/includes/header.php';

if ($role === 'super_admin') {
    require __DIR__ . '/modules/dashboard/super_admin.php';
} elseif ($role === 'admin') {
    require __DIR__ . '/modules/dashboard/admin.php';
} else {
    require __DIR__ . '/modules/dashboard/employee.php';
}

require_once __DIR__ . '/includes/footer.php';
