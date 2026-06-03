<?php
$stats = [
    ['label' => 'Employees', 'value' => user_count_by_role('employee')],
    ['label' => 'Admins', 'value' => user_count_by_role('admin')],
    ['label' => 'Departments', 'value' => count(fetch_departments())],
    ['label' => 'Records', 'value' => (int) db()->query('SELECT COUNT(*) FROM department_records')->fetchColumn()],
];
$departmentStats = department_statistics();
$deptData = array_map(static function (array $department) use ($departmentStats): array {
    $match = array_values(array_filter($departmentStats, static fn(array $row): bool => $row['department_name'] === $department['department_name']))[0] ?? null;

    return [
        'label' => $department['department_name'],
        'value' => (int) ($match['employee_count'] ?? 0),
    ];
}, fetch_departments());
?>
<section class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="section-badge bg-dark text-white"><i class="fa-solid fa-crown"></i> Super Admin Dashboard</span>
            <h1 class="display-6 fw-bold mt-3 mb-1">Enterprise control center</h1>
            <p class="text-secondary mb-0">Monitor the entire HRMS, manage departments, and maintain governance from one view.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-cimen" href="<?= e(base_url('modules/admins/index.php')) ?>">Manage Admins</a>
            <a class="btn btn-outline-cimen" href="<?= e(base_url('modules/departments/index.php')) ?>">Manage Departments</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <?php foreach ($stats as $stat): ?>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card p-4 h-100">
                    <div class="metric-label text-uppercase fw-semibold"><?= e($stat['label']) ?></div>
                    <div class="metric-value mt-2"><?= e((string) $stat['value']) ?></div>
                    <div class="small text-secondary mt-2">Updated in real time from MySQL.</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="dashboard-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Department workforce overview</h2>
                    <span class="soft-badge badge rounded-pill">Live chart</span>
                </div>
                <div class="canvas-wrap" data-bar-chart='<?= json_encode($deptData, JSON_UNESCAPED_UNICODE) ?>'></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="dashboard-card p-4 h-100">
                <h2 class="h5 mb-3">Latest operational access</h2>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action d-flex justify-content-between" href="<?= e(base_url('modules/employees/index.php')) ?>">
                        Employee registry <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between" href="<?= e(base_url('modules/admins/index.php')) ?>">
                        Administrator registry <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between" href="<?= e(base_url('pdf/export.php?type=employees')) ?>">
                        Export employee list <i class="fa-solid fa-file-pdf"></i>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between" href="<?= e(base_url('modules/departments/index.php')) ?>">
                        Department master data <i class="fa-solid fa-building"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
