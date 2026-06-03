<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$generatedLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $email = clean_input($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter a valid email address.');
    } else {
        $user = find_user_by_email($email);
        if (!$user) {
            flash('error', 'No account was found for that email address.');
        } else {
            $token = send_reset_token((int) $user['id']);
            $generatedLink = base_url('reset-password.php?email=' . urlencode($email) . '&token=' . urlencode($token));
            flash('info', 'Password reset token generated. Use the link shown below.');
        }
    }
}

$pageTitle = 'Forgot Password | CIMEN HRMS';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container d-flex justify-content-center">
    <div class="auth-card p-4 p-lg-5 w-100" style="max-width: 560px;">
        <div class="text-center mb-4">
            <span class="section-badge bg-warning text-dark"><i class="fa-solid fa-unlock-keyhole"></i> Password Recovery</span>
            <h1 class="h3 fw-bold mt-3 mb-2">Forgot your password?</h1>
            <p class="text-secondary mb-0">Enter your email address and we will generate a reset token for development use.</p>
        </div>
        <form method="post" data-validate="true" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <button class="btn btn-cimen w-100 py-3" type="submit">Generate Reset Token</button>
        </form>

        <?php if ($generatedLink): ?>
            <div class="alert alert-info mt-4 mb-0">
                <div class="fw-semibold mb-1">Simulated email link</div>
                <a href="<?= e($generatedLink) ?>"><?= e($generatedLink) ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
