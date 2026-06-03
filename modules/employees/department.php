<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// allow admins and employees to view department details
require_role(['admin', 'super_admin', 'employee']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid department id');
}

$stmt = db()->prepare('SELECT * FROM departments WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$department = $stmt->fetch();
if (!$department) {
    http_response_code(404);
    exit('Department not found');
}

$empStmt = db()->prepare('SELECT id, full_name, username, email FROM users WHERE department_id = :id AND role = "employee" ORDER BY full_name ASC');
$empStmt->execute(['id' => $id]);
$employees = $empStmt->fetchAll();

$pageTitle = 'Department: ' . e($department['department_name']);
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?= e($department['department_name']) ?></h1>
        <a class="btn btn-outline-cimen" href="<?= e(base_url('modules/employees/index.php')) ?>">Back to Employees</a>
    </div>

    <div class="dashboard-card p-4">
        <h2 class="h6 mb-3">Employees in this department</h2>
        <?php if (count($employees) === 0): ?>
            <p class="text-muted">No employees found in this department.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr><th>#</th><th>Name</th><th>Username</th><th>Email</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $i => $emp): ?>
                            <tr>
                                <td><?= (int) $i + 1 ?></td>
                                <td><?= e($emp['full_name']) ?></td>
                                <td><?= e($emp['username']) ?></td>
                                <td><?= e($emp['email']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
