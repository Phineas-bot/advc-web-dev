<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function is_username_taken(string $username, ?int $excludeId = null): bool
{
    $sql = 'SELECT COUNT(*) FROM users WHERE username = :username';
    $params = ['username' => $username];

    if ($excludeId !== null) {
        $sql .= ' AND id <> :exclude_id';
        $params['exclude_id'] = $excludeId;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

function is_email_taken(string $email, ?int $excludeId = null): bool
{
    $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
    $params = ['email' => $email];

    if ($excludeId !== null) {
        $sql .= ' AND id <> :exclude_id';
        $params['exclude_id'] = $excludeId;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

function create_user(array $data): int
{
    $stmt = db()->prepare('INSERT INTO users (full_name, address, email, phone, username, password, role, department_id, created_at, updated_at) VALUES (:full_name, :address, :email, :phone, :username, :password, :role, :department_id, NOW(), NOW())');
    $stmt->execute([
        'full_name' => $data['full_name'],
        'address' => $data['address'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'username' => $data['username'],
        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        'role' => $data['role'],
        'department_id' => $data['department_id'],
    ]);

    return (int) db()->lastInsertId();
}

function find_user_by_username(string $username): ?array
{
    $stmt = db()->prepare('SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function authenticate_user(string $username, string $password): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string) $user['password'])) {
        return null;
    }

    return $user;
}

function store_reset_link(int $userId, string $email): array
{
    $token = send_reset_token($userId);
    return [
        'token' => $token,
        'link' => base_url('reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($email)),
    ];
}

function find_reset_request(string $email, string $token): ?array
{
    $stmt = db()->prepare('SELECT pr.*, u.id AS user_id FROM password_resets pr INNER JOIN users u ON u.id = pr.user_id WHERE u.email = :email AND pr.token_hash = :token_hash AND pr.used_at IS NULL AND pr.expires_at > NOW() ORDER BY pr.id DESC LIMIT 1');
    $stmt->execute([
        'email' => $email,
        'token_hash' => hash('sha256', $token),
    ]);

    $reset = $stmt->fetch();
    return $reset ?: null;
}

function mark_reset_used(int $id): void
{
    $stmt = db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
