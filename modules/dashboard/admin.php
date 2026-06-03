<?php
$user = current_user();
$departmentId = (int) ($user['department_id'] ?? 0);
$departmentStats = array_values(array_filter(department_statistics(), static fn(array $item): bool => (int) $item['id'] === $departmentId));
$departmentStats = $departmentStats[0] ?? ['department_name' => $user['department_name'] ?? 'Unassigned', 'employee_count' => 0, 'record_count' => 0];
?>
<section class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="section-badge bg-primary text-white"><i class="fa-solid fa-user-gear"></i> Admin Dashboard</span>
            <h1 class="display-6 fw-bold mt-3 mb-1"><?= e($departmentStats['department_name']) ?></h1>
            <p class="text-secondary mb-0">Manage employees and department records for your assigned department.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-cimen" href="<?= e(base_url('department.php?id=' . $departmentId)) ?>">Open Department</a>
            <a class="btn btn-outline-cimen" href="<?= e(base_url('modules/employees/index.php')) ?>">Employees</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="metric-label text-uppercase fw-semibold">Department Employees</div>
                <div class="metric-value mt-2"><?= e((string) $departmentStats['employee_count']) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="metric-label text-uppercase fw-semibold">Department Records</div>
                <div class="metric-value mt-2"><?= e((string) $departmentStats['record_count']) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="metric-label text-uppercase fw-semibold">Your Role</div>
                <div class="metric-value mt-2">Admin</div>
            </div>
        </div>
    </div>

    <div class="dashboard-card p-4">
        <h2 class="h5 mb-3">Department operations</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <a class="btn btn-outline-cimen w-100 py-3" href="<?= e(base_url('department.php?id=' . $departmentId)) ?>">View and manage records</a>
            </div>
            <div class="col-md-6">
                <a class="btn btn-cimen w-100 py-3" href="<?= e(base_url('pdf/export.php?type=department_records&department_id=' . $departmentId)) ?>">Export department report</a>
            </div>
        </div>
    </div>
</section>
