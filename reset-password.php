<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$email = clean_input($_GET['email'] ?? $_POST['email'] ?? '');
$token = clean_input($_GET['token'] ?? $_POST['token'] ?? '');
$resetRequest = $email !== '' && $token !== '' ? find_reset_request($email, $token) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (!$resetRequest) {
        flash('error', 'The reset token is invalid or has expired.');
        redirect('forgot-password.php');
    }

    if ($password !== $confirmPassword) {
        flash('error', 'Password confirmation does not match.');
    } else {
        $errors = password_rules_errors($password);
        if ($errors) {
            flash('error', implode(' ', $errors));
        } else {
            $stmt = db()->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'id' => $resetRequest['user_id'],
            ]);

            mark_reset_used((int) $resetRequest['id']);
            flash('success', 'Password updated successfully. Please sign in.');
            redirect('login.php');
        }
    }
}

$pageTitle = 'Reset Password | CIMEN HRMS';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container d-flex justify-content-center">
    <div class="auth-card p-4 p-lg-5 w-100" style="max-width: 560px;">
        <div class="text-center mb-4">
            <span class="section-badge bg-info text-dark"><i class="fa-solid fa-key"></i> Reset Password</span>
            <h1 class="h3 fw-bold mt-3 mb-2">Create a new password</h1>
            <p class="text-secondary mb-0">Use the token generated from the password recovery flow.</p>
        </div>
        <?php if (!$resetRequest): ?>
            <div class="alert alert-danger">This reset link is invalid or has expired. Please request a new one.</div>
        <?php else: ?>
            <form method="post" data-validate="true" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= e($email) ?>">
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                </div>
                <button class="btn btn-cimen w-100 py-3" type="submit">Update Password</button>
            </form>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
