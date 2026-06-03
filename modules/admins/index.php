<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('super_admin');

$editAdmin = null;

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
        $departmentId = (int) ($_POST['department_id'] ?? 0);

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
            $stmt = db()->prepare('UPDATE users SET full_name = :full_name, address = :address, email = :email, phone = :phone, username = :username, department_id = :department_id, updated_at = NOW()' . ($password !== '' ? ', password = :password' : '') . ' WHERE id = :id AND role = "admin"');
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
            flash('success', 'Admin updated successfully.');
            redirect('modules/admins/index.php');
        } else {
            create_user([
                'full_name' => $fullName,
                'address' => $address,
                'email' => $email,
                'phone' => $phone,
                'username' => $username,
                'password' => $password,
                'role' => 'admin',
                'department_id' => $departmentId,
            ]);
            flash('success', 'Admin created successfully.');
            redirect('modules/admins/index.php');
        }
    }

    if ($postAction === 'delete') {
        $stmt = db()->prepare('DELETE FROM users WHERE id = :id AND role = "admin"');
        $stmt->execute(['id' => (int) $_POST['id']]);
        flash('success', 'Admin deleted successfully.');
        redirect('modules/admins/index.php');
    }
}

if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id AND role = "admin" LIMIT 1');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editAdmin = $stmt->fetch() ?: null;
}

$adminsStmt = db()->query('SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.role = "admin" ORDER BY u.full_name ASC');
$admins = $adminsStmt->fetchAll();
$departments = fetch_departments();

$pageTitle = 'Admins | CIMEN HRMS';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="section-badge bg-dark text-white"><i class="fa-solid fa-user-shield"></i> Admin Registry</span>
            <h1 class="display-6 fw-bold mt-3 mb-1">Administrators</h1>
            <p class="text-secondary mb-0">Create and maintain department administrators.</p>
        </div>
        <a class="btn btn-outline-cimen" href="<?= e(base_url('dashboard.php')) ?>">Dashboard</a>
    </div>

    <div class="dashboard-card p-4 mb-4">
        <h2 class="h5 mb-3"><?= $editAdmin ? 'Edit Admin' : 'Add Admin' ?></h2>
        <form method="post" data-validate="true" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e((string) ($editAdmin['id'] ?? '')) ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required value="<?= e($editAdmin['full_name'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= e($editAdmin['email'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= e($editAdmin['phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required value="<?= e($editAdmin['username'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($editAdmin['address'] ?? '') ?>"></div>
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select department</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= (int) $department['id'] ?>" <?= ((int) ($editAdmin['department_id'] ?? 0) === (int) $department['id']) ? 'selected' : '' ?>><?= e($department['department_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Password <?= $editAdmin ? '(optional)' : '' ?></label><input type="password" name="password" class="form-control" <?= $editAdmin ? '' : 'required' ?> minlength="8"></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-cimen px-4" type="submit"><?= $editAdmin ? 'Update Admin' : 'Save Admin' ?></button>
                <?php if ($editAdmin): ?><a class="btn btn-outline-cimen px-4" href="<?= e(base_url('modules/admins/index.php')) ?>">Cancel</a><?php endif; ?>
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
                    <?php foreach ($admins as $index => $admin): ?>
                        <tr>
                            <td><?= (int) $index + 1 ?></td>
                            <td><?= e($admin['full_name']) ?></td>
                            <td><?= e($admin['username']) ?></td>
                            <td><?= e($admin['email']) ?></td>
                            <td><?= e($admin['department_name'] ?? '') ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-cimen" href="<?= e(base_url('modules/admins/index.php?edit=' . (int) $admin['id'])) ?>">Edit</a>
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this admin?">Delete</button>
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
