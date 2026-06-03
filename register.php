<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$departments = fetch_departments();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $fullName = clean_input($_POST['full_name'] ?? '');
    $address = clean_input($_POST['address'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $username = clean_input($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $departmentId = (int) ($_POST['department_id'] ?? 0);

    $errors = [];
    if ($fullName === '' || $email === '' || $username === '' || $password === '' || $departmentId <= 0) {
        $errors[] = 'Please complete all required fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (is_username_taken($username)) {
        $errors[] = 'Username already exists.';
    }
    if (is_email_taken($email)) {
        $errors[] = 'Email already exists.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }
    $passwordErrors = password_rules_errors($password);
    $errors = array_merge($errors, $passwordErrors);

    if ($errors) {
        flash('error', implode(' ', $errors));
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

        flash('success', 'Registration successful. You can now log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Register | CIMEN HRMS';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="auth-card p-4 p-lg-5">
                <div class="text-center mb-4">
                    <span class="section-badge bg-success text-white"><i class="fa-solid fa-user-plus"></i> Employee Registration</span>
                    <h1 class="h3 fw-bold mt-3 mb-2">Create your employee account</h1>
                    <p class="text-secondary mb-0">Registration is limited to employees. Admin accounts are created by Super Admin only.</p>
                </div>
                <form method="post" data-validate="true" novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required value="<?= e($_POST['full_name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($_POST['address'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required minlength="4" value="<?= e($_POST['username'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Department</label><select name="department_id" class="form-select" required><option value="">Select department</option><?php foreach ($departments as $department): ?><option value="<?= (int) $department['id'] ?>" <?= ((int) ($_POST['department_id'] ?? 0) === (int) $department['id']) ? 'selected' : '' ?>><?= e($department['department_name']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" minlength="8" required></div>
                        <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" class="form-control" minlength="8" required></div>
                    </div>
                    <div class="d-grid d-md-flex gap-3 mt-4">
                        <button class="btn btn-cimen px-4" type="submit">Create Account</button>
                        <a class="btn btn-outline-cimen px-4" href="<?= e(base_url('login.php')) ?>">Already registered? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
