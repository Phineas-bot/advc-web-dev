<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $fullName = clean_input($_POST['full_name'] ?? '');
    $address = clean_input($_POST['address'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($fullName === '' || $email === '') {
        flash('error', 'Full name and email are required.');
        redirect('profile.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter a valid email address.');
        redirect('profile.php');
    }

    if (is_email_taken($email, (int) $user['id'])) {
        flash('error', 'That email address is already in use.');
        redirect('profile.php');
    }

    $params = [
        'full_name' => $fullName,
        'address' => $address,
        'phone' => $phone,
        'email' => $email,
        'id' => $user['id'],
    ];

    $passwordSql = '';
    if ($password !== '' || $confirmPassword !== '') {
        if ($password !== $confirmPassword) {
            flash('error', 'Password confirmation does not match.');
            redirect('profile.php');
        }

        $passwordErrors = password_rules_errors($password);
        if ($passwordErrors) {
            flash('error', implode(' ', $passwordErrors));
            redirect('profile.php');
        }

        $passwordSql = ', password = :password';
        $params['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $stmt = db()->prepare('UPDATE users SET full_name = :full_name, address = :address, phone = :phone, email = :email' . $passwordSql . ', updated_at = NOW() WHERE id = :id');
    $stmt->execute($params);
    flash('success', 'Profile updated successfully.');
    redirect('profile.php');
}

$pageTitle = 'Profile | CIMEN HRMS';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="auth-card p-4 p-lg-5">
                <h1 class="h3 fw-bold mb-2">Update your profile</h1>
                <p class="text-secondary mb-4">Keep your contact details current and change your password when required.</p>
                <form method="post" data-validate="true" novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required value="<?= e($user['full_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="<?= e($user['address']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="8">
                        </div>
                    </div>
                    <div class="d-flex gap-3 mt-4">
                        <button class="btn btn-cimen px-4" type="submit">Save Changes</button>
                        <a class="btn btn-outline-cimen px-4" href="<?= e(base_url('dashboard.php')) ?>">Back to Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
