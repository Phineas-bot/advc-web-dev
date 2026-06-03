<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role(['admin', 'super_admin']);

$user = current_user();
$isSuperAdmin = is_super_admin();
$isDepartmentAdmin = !$isSuperAdmin;
$formMode = 'create';
$editEmployee = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $fullName = clean_input($_POST['full_name'] ?? '');
        $address = clean_input($_POST['address'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $username = clean_input($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $departmentId = $isSuperAdmin ? (int) ($_POST['department_id'] ?? 0) : (int) ($user['department_id'] ?? 0);

        $errors = [];
        if ($fullName === '' || $email === '' || $username === '' || ($id === 0 && $password === '')) {
            $errors[] = 'Please complete all required fields.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if ($departmentId <= 0) {
            $errors[] = 'Select a department.';
        }
        if (is_username_taken($username, $id > 0 ? $id : null)) {
            $errors[] = 'Username already exists.';
        }
        if (is_email_taken($email, $id > 0 ? $id : null)) {
            $errors[] = 'Email already exists.';
        }
        if ($password !== '' && ($passwordErrors = password_rules_errors($password))) {
            $errors = array_merge($errors, $passwordErrors);
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
        } elseif ($id > 0) {
            $stmt = db()->prepare('UPDATE users SET full_name = :full_name, address = :address, email = :email, phone = :phone, username = :username, department_id = :department_id, updated_at = NOW()' . ($password !== '' ? ', password = :password' : '') . ' WHERE id = :id AND role = "employee"');
            $params = [
                'full_name' => $fullName,
                'address' => $address,
                'email' => $email,
                'phone' => $phone,
                'username' => $username,
                'department_id' => $departmentId,
                'id' => $id,
            ];
            if ($password !== '') {
                $params['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $stmt->execute($params);
            flash('success', 'Employee updated successfully.');
            redirect('modules/employees/index.php');
        } else {
            create_user([
                'full_name' => $fullName,
                'address' => $address,
                'email' => $email,
                'phone' => $phone,
                'username' => $username,
                'password' => $password,
                'role' => 'employee',
                'department_id' => $departmentId,
            ]);
            flash('success', 'Employee created successfully.');
            redirect('modules/employees/index.php');
        }
    }

    if ($postAction === 'delete') {
        $stmt = db()->prepare('DELETE FROM users WHERE id = :id AND role = "employee"');
        $stmt->execute(['id' => (int) $_POST['id']]);
        flash('success', 'Employee deleted successfully.');
        redirect('modules/employees/index.php');
    }
}

if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id AND role = "employee" LIMIT 1');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editEmployee = $stmt->fetch() ?: null;
    if ($editEmployee && $isDepartmentAdmin && (int) $editEmployee['department_id'] !== (int) $user['department_id']) {
        http_response_code(403);
        exit('You can only edit employees in your department.');
    }
}

$employeesSql = 'SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.role = "employee"';
$employeeParams = [];
if ($isDepartmentAdmin) {
    $employeesSql .= ' AND u.department_id = :department_id';
    $employeeParams['department_id'] = $user['department_id'];
}
$employeesSql .= ' ORDER BY u.full_name ASC';
$employeesStmt = db()->prepare($employeesSql);
$employeesStmt->execute($employeeParams);
$employees = $employeesStmt->fetchAll();
$departments = fetch_departments();

$pageTitle = 'Employees | CIMEN HRMS';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="section-badge bg-success text-white"><i class="fa-solid fa-users"></i> Employee Registry</span>
            <h1 class="display-6 fw-bold mt-3 mb-1">Employees</h1>
            <p class="text-secondary mb-0">Create, update, and remove employee records.</p>
        </div>
        <a class="btn btn-outline-cimen" href="<?= e(base_url('/dashboard.php')) ?>">Dashboard</a>
    </div>

    <div class="dashboard-card p-4 mb-4">
        <h2 class="h5 mb-3"><?= $editEmployee ? 'Edit Employee' : 'Add Employee' ?></h2>
        <form method="post" data-validate="true" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e((string) ($editEmployee['id'] ?? '')) ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required value="<?= e($editEmployee['full_name'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= e($editEmployee['email'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= e($editEmployee['phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required value="<?= e($editEmployee['username'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($editEmployee['address'] ?? '') ?>"></div>
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <?php if ($isSuperAdmin): ?>
                        <select name="department_id" class="form-select" required>
                            <option value="">Select department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?= (int) $department['id'] ?>" <?= ((int) ($editEmployee['department_id'] ?? $user['department_id']) === (int) $department['id']) ? 'selected' : '' ?>><?= e($department['department_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" value="<?= e($user['department_name'] ?? department_name((int) ($user['department_id'] ?? 0))) ?>" disabled>
                        <input type="hidden" name="department_id" value="<?= (int) ($user['department_id'] ?? 0) ?>">
                    <?php endif; ?>
                </div>
                <div class="col-md-6"><label class="form-label">Password <?= $editEmployee ? '(optional)' : '' ?></label><input type="password" name="password" class="form-control" <?= $editEmployee ? '' : 'required' ?> minlength="8"></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-cimen px-4" type="submit"><?= $editEmployee ? 'Update Employee' : 'Save Employee' ?></button>
                <?php if ($editEmployee): ?><a class="btn btn-outline-cimen px-4" href="<?= e(base_url('/modules/employees/index.php')) ?>">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="dashboard-card p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $index => $employee): ?>
                        <tr>
                            <td><?= (int) $index + 1 ?></td>
                            <td><?= e($employee['full_name']) ?></td>
                            <td><?= e($employee['username']) ?></td>
                            <td><?= e($employee['email']) ?></td>
                            <td><?= e($employee['department_name'] ?? '') ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-cimen" href="<?= e(base_url('/modules/employees/index.php?edit=' . (int) $employee['id'])) ?>">Edit</a>
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $employee['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this employee?">Delete</button>
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
