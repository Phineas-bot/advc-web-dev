<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $username = clean_input($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember_me']);

    $user = authenticate_user($username, $password);

    if ($user) {
        login_user($user);
        remember_login($user, $remember);
        flash('success', 'Welcome back, ' . $user['full_name'] . '.');
        redirect('dashboard.php');
    }

    flash('error', 'Invalid username or password.');
}

$pageTitle = 'Login | CIMEN HRMS';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container d-flex justify-content-center">
    <div class="auth-card p-4 p-lg-5 w-100" style="max-width: 560px;">
        <div class="text-center mb-4">
            <span class="hero-badge bg-primary-subtle text-primary-emphasis border-0"><i class="fa-solid fa-right-to-bracket"></i> Secure Sign In</span>
            <h1 class="h3 fw-bold mt-3 mb-2">Login to CIMEN HRMS</h1>
            <p class="text-secondary mb-0">Use your employee username and password to continue.</p>
        </div>
        <form method="post" data-validate="true" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required minlength="3" autocomplete="username" value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" autocomplete="current-password">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me">
                    <label class="form-check-label" for="remember_me">Remember me</label>
                </div>
                <a href="<?= e(base_url('forgot-password.php')) ?>" class="text-decoration-none">Forgot password?</a>
            </div>
            <button class="btn btn-cimen w-100 py-3 fw-semibold" type="submit">Sign In</button>
            <div class="text-center mt-4">
                <span class="text-secondary">New employee?</span> <a href="<?= e(base_url('register.php')) ?>">Create account</a>
            </div>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
