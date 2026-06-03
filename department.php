<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$departmentId = (int) ($_GET['id'] ?? 0);
$departmentStmt = db()->prepare('SELECT * FROM departments WHERE id = :id LIMIT 1');
$departmentStmt->execute(['id' => $departmentId]);
$department = $departmentStmt->fetch();

if (!$department) {
    http_response_code(404);
    exit('Department not found.');
}

$user = current_user();
$canManage = is_super_admin() || ((int) ($user['department_id'] ?? 0) === $departmentId && is_admin());
$canView = is_super_admin() || is_admin() || ((int) ($user['department_id'] ?? 0) === $departmentId && ($_SESSION['role'] ?? '') === 'employee');

if (!$canView) {
    http_response_code(403);
    exit('You cannot access this department.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    if (!$canManage) {
        http_response_code(403);
        exit('You are not allowed to manage records here.');
    }

    $postAction = $_POST['action'] ?? '';
    $fields = [];
    for ($i = 1; $i <= 10; $i++) {
        $fields['field' . $i] = clean_input($_POST['field' . $i] ?? '');
    }

    if ($postAction === 'save') {
        $createdBy = (int) $user['id'];
        $recordId = (int) ($_POST['id'] ?? 0);

        if ($recordId > 0) {
            $sql = 'UPDATE department_records SET ';
            $assignments = [];
            for ($i = 1; $i <= 10; $i++) {
                $assignments[] = 'field' . $i . ' = :field' . $i;
            }
            $sql .= implode(', ', $assignments) . ', updated_at = NOW() WHERE id = :id AND department_id = :department_id';
            $stmt = db()->prepare($sql);
            $stmt->execute($fields + ['id' => $recordId, 'department_id' => $departmentId]);
            flash('success', 'Department record updated successfully.');
        } else {
            $sql = 'INSERT INTO department_records (department_id, ';
            $sql .= implode(', ', array_keys($fields));
            $sql .= ', created_by, created_at, updated_at) VALUES (:department_id, ';
            $sql .= implode(', ', array_map(static fn($field) => ':' . $field, array_keys($fields)));
            $sql .= ', :created_by, NOW(), NOW())';
            $stmt = db()->prepare($sql);
            $stmt->execute($fields + ['department_id' => $departmentId, 'created_by' => $createdBy]);
            flash('success', 'Department record created successfully.');
        }

        redirect('department.php?id=' . $departmentId);
    }

    if ($postAction === 'delete') {
        $stmt = db()->prepare('DELETE FROM department_records WHERE id = :id AND department_id = :department_id');
        $stmt->execute([
            'id' => (int) $_POST['id'],
            'department_id' => $departmentId,
        ]);
        flash('success', 'Department record deleted successfully.');
        redirect('department.php?id=' . $departmentId);
    }
}

$editRecord = null;
if (isset($_GET['edit']) && $canManage) {
    $stmt = db()->prepare('SELECT * FROM department_records WHERE id = :id AND department_id = :department_id LIMIT 1');
    $stmt->execute([
        'id' => (int) $_GET['edit'],
        'department_id' => $departmentId,
    ]);
    $editRecord = $stmt->fetch() ?: null;
}

$recordsStmt = db()->prepare('SELECT dr.*, u.full_name AS creator_name FROM department_records dr LEFT JOIN users u ON u.id = dr.created_by WHERE dr.department_id = :department_id ORDER BY dr.id DESC');
$recordsStmt->execute(['department_id' => $departmentId]);
$records = $recordsStmt->fetchAll();

$pageTitle = $department['department_name'] . ' Department | CIMEN HRMS';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="section-badge bg-primary text-white"><i class="fa-solid fa-building-user"></i> Department View</span>
            <h1 class="display-6 fw-bold mt-3 mb-1"><?= e($department['department_name']) ?></h1>
            <p class="text-secondary mb-0"><?= e($department['description'] ?? 'Department records') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-cimen" href="<?= e(base_url('dashboard.php')) ?>">Dashboard</a>
            <a class="btn btn-cimen" href="<?= e(base_url('pdf/export.php?type=department_records&department_id=' . $departmentId)) ?>">Export PDF</a>
        </div>
    </div>

    <?php if ($canManage): ?>
        <div class="dashboard-card p-4 mb-4">
            <h2 class="h5 mb-3"><?= $editRecord ? 'Edit Record' : 'Add Record' ?></h2>
            <form method="post" data-validate="true" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= e((string) ($editRecord['id'] ?? '')) ?>">
                <div class="row g-3">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <div class="col-md-6">
                            <label class="form-label">Field <?= $i ?></label>
                            <input type="text" name="field<?= $i ?>" class="form-control" required value="<?= e($editRecord['field' . $i] ?? '') ?>">
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="mt-4">
                    <button class="btn btn-cimen px-4" type="submit"><?= $editRecord ? 'Update Record' : 'Save Record' ?></button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="dashboard-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <?php for ($i = 1; $i <= 10; $i++): ?><th>Field <?= $i ?></th><?php endfor; ?>
                        <th>Created By</th>
                        <?php if ($canManage): ?><th class="text-end">Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $index => $record): ?>
                        <tr>
                            <td><?= (int) $index + 1 ?></td>
                            <?php for ($i = 1; $i <= 10; $i++): ?><td><?= e($record['field' . $i]) ?></td><?php endfor; ?>
                            <td><?= e($record['creator_name'] ?? 'System') ?></td>
                            <?php if ($canManage): ?>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-cimen" href="<?= e(base_url('department.php?id=' . $departmentId . '&edit=' . (int) $record['id'])) ?>">Edit</a>
                                    <form method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this department record?">Delete</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
