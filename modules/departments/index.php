<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('super_admin');

$editDepartment = null;
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'save') {
        $departmentName = clean_input($_POST['department_name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');

        if ($departmentName === '') {
            flash('error', 'Department name is required.');
        } elseif (!empty($_POST['id'])) {
            $stmt = db()->prepare('UPDATE departments SET department_name = :department_name, description = :description WHERE id = :id');
            $stmt->execute([
                'department_name' => $departmentName,
                'description' => $description,
                'id' => (int) $_POST['id'],
            ]);
            flash('success', 'Department updated successfully.');
        } else {
            $stmt = db()->prepare('INSERT INTO departments (department_name, description, created_at) VALUES (:department_name, :description, NOW())');
            $stmt->execute([
                'department_name' => $departmentName,
                'description' => $description,
            ]);
            flash('success', 'Department created successfully.');
        }
        redirect('modules/departments/index.php');
    }

    if ($postAction === 'delete') {
        $stmt = db()->prepare('DELETE FROM departments WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        flash('success', 'Department deleted successfully.');
        redirect('modules/departments/index.php');
    }
}

if ($action === 'edit' && !empty($_GET['id'])) {
    $stmt = db()->prepare('SELECT * FROM departments WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_GET['id']]);
    $editDepartment = $stmt->fetch() ?: null;
}

$departments = fetch_departments();
$employeeCountStmt = db()->prepare('SELECT COUNT(*) FROM users WHERE department_id = :department_id AND role = "employee"');
$pageTitle = 'Manage Departments | CIMEN HRMS';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="section-badge bg-dark text-white"><i class="fa-solid fa-building"></i> Department Master</span>
            <h1 class="display-6 fw-bold mt-3 mb-1">Department management</h1>
            <p class="text-secondary mb-0">Create and maintain the company department list.</p>
        </div>
        <a class="btn btn-outline-cimen" href="<?= e(base_url('/dashboard.php')) ?>">Back to Dashboard</a>
    </div>

    <div class="dashboard-card p-4 mb-4">
        <h2 class="h5 mb-3"><?= $editDepartment ? 'Edit Department' : 'Add Department' ?></h2>
        <form method="post" data-validate="true" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e((string) ($editDepartment['id'] ?? '')) ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="department_name" class="form-control" required value="<?= e($editDepartment['department_name'] ?? '') ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="<?= e($editDepartment['description'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-cimen" type="submit"><?= $editDepartment ? 'Update' : 'Save' ?></button>
                </div>
            </div>
        </form>
    </div>

    <div class="dashboard-card p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Department</th>
                        <th>Description</th>
                        <th>Employees</th>
                        <th>Records</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $index => $department): ?>
                        <tr>
                            <td><?= (int) $index + 1 ?></td>
                            <td><?= e($department['department_name']) ?></td>
                            <td><?= e($department['description'] ?? '') ?></td>
                            <?php $employeeCountStmt->execute(['department_id' => $department['id']]); ?>
                            <td><?= (int) $employeeCountStmt->fetchColumn() ?></td>
                            <td><?= department_record_count((int) $department['id']) ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-cimen" href="<?= e(base_url('/modules/departments/index.php?action=edit&id=' . (int) $department['id'])) ?>">Edit</a>
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $department['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this department and its linked records?">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
