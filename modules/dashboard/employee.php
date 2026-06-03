<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('employee');

$user = current_user();
?>
<section class="container">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-4">
            <div class="dashboard-card p-4 h-100 text-center">
                <div class="avatar-circle mx-auto mb-3"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></div>
                <h1 class="h3 fw-bold mb-1"><?= e($user['full_name']) ?></h1>
                <p class="text-secondary mb-3"><?= e($user['department_name'] ?? 'Unassigned Department') ?></p>
                <div class="d-grid gap-2">
                    <a class="btn btn-cimen" href="<?= e(base_url('/profile.php')) ?>">Update Profile</a>
                    <a class="btn btn-outline-cimen" href="<?= e(base_url('/register.php')) ?>">Refer another employee</a>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="dashboard-card p-4 h-100">
                <span class="section-badge bg-success text-white"><i class="fa-solid fa-address-card"></i> Employee Profile</span>
                <h2 class="h4 mt-3 mb-3">Your account details</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light">
                            <div class="text-secondary small">Username</div>
                            <div class="fw-semibold"><?= e($user['username']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light">
                            <div class="text-secondary small">Email</div>
                            <div class="fw-semibold"><?= e($user['email']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light">
                            <div class="text-secondary small">Phone</div>
                            <div class="fw-semibold"><?= e($user['phone']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light">
                            <div class="text-secondary small">Department</div>
                            <div class="fw-semibold"><?= e($user['department_name'] ?? 'Unassigned') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
