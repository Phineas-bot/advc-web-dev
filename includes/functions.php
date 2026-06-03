<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

date_default_timezone_set('Africa/Accra');

function base_url(string $path = ''): string
{
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $base = rtrim($base, '/');

    if ($base === '/' || $base === '.') {
        $base = '';
    }

    return $base . '/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return base_url($path);
}

function asset(string $path = ''): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function clean_input(mixed $value): string
{
    return trim((string) $value);
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function flash(string $key, ?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function validate_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) $token)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

function verify_csrf(): void
{
    validate_csrf();
}

function csrf_input(): string
{
    return csrf_field();
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function set_old(array $values): void
{
    $_SESSION['old'] = $values;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $stmt = db()->prepare('SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $cached = $stmt->fetch() ?: null;
    return $cached;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
}

function logout_user(): void
{
    if (isset($_SESSION['user_id'])) {
        $stmt = db()->prepare('DELETE FROM remember_tokens WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
    }

    setcookie('remember_me', '', time() - 3600, '/');
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.', 'warning');
        redirect('login.php');
    }
}

function require_role(array|string $roles): void
{
    require_login();
    $roles = (array) $roles;

    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function is_admin(): bool
{
    return in_array($_SESSION['role'] ?? '', ['admin', 'super_admin'], true);
}

function is_super_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'super_admin';
}

function remember_login(array $user, bool $remember): void
{
    if (!$remember) {
        return;
    }

    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
    $expiresAt = (new DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

    $stmt = db()->prepare('INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at, created_at) VALUES (:user_id, :selector, :validator_hash, :expires_at, NOW())');
    $stmt->execute([
        'user_id' => $user['id'],
        'selector' => $selector,
        'validator_hash' => $hashedValidator,
        'expires_at' => $expiresAt,
    ]);

    setcookie('remember_me', $selector . ':' . $validator, [
        'expires' => time() + (30 * 24 * 60 * 60),
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function auto_login_from_cookie(): void
{
    if (is_logged_in() || empty($_COOKIE['remember_me'])) {
        return;
    }

    $parts = explode(':', (string) $_COOKIE['remember_me'], 2);
    if (count($parts) !== 2) {
        return;
    }

    [$selector, $validator] = $parts;
    $stmt = db()->prepare('SELECT rt.*, u.id, u.role FROM remember_tokens rt INNER JOIN users u ON u.id = rt.user_id WHERE rt.selector = :selector AND rt.expires_at > NOW() LIMIT 1');
    $stmt->execute(['selector' => $selector]);
    $token = $stmt->fetch();

    if (!$token || !password_verify($validator, $token['validator_hash'])) {
        return;
    }

    login_user(['id' => $token['id'], 'role' => $token['role']]);
}

function department_name(?int $departmentId): string
{
    if (!$departmentId) {
        return 'Unassigned';
    }

    $stmt = db()->prepare('SELECT department_name FROM departments WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $departmentId]);
    return (string) ($stmt->fetchColumn() ?: 'Unknown');
}

function fetch_departments(): array
{
    return db()->query('SELECT id, department_name, description FROM departments ORDER BY department_name ASC')->fetchAll();
}

function department_statistics(): array
{
    return db()->query('SELECT d.id, d.department_name, COUNT(u.id) AS employee_count, COUNT(dr.id) AS record_count FROM departments d LEFT JOIN users u ON u.department_id = d.id AND u.role = "employee" LEFT JOIN department_records dr ON dr.department_id = d.id GROUP BY d.id, d.department_name ORDER BY d.department_name ASC')->fetchAll();
}

function user_count_by_role(string $role): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
    $stmt->execute(['role' => $role]);
    return (int) $stmt->fetchColumn();
}

function password_rules_errors(string $password): array
{
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain an uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain a lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain a number.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain a symbol.';
    }

    return $errors;
}

function send_reset_token(int $userId): string
{
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = (new DateTimeImmutable('+60 minutes'))->format('Y-m-d H:i:s');

    $stmt = db()->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, :expires_at, NOW())');
    $stmt->execute([
        'user_id' => $userId,
        'token_hash' => $tokenHash,
        'expires_at' => $expiresAt,
    ]);

    return $token;
}

auto_login_from_cookie();
