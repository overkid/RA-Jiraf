<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crm.php';

function client_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps =
        (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function client_auth_user(PDO $pdo): ?array
{
    client_session_start();
    $clientId = (int) ($_SESSION['client_id'] ?? 0);
    if ($clientId <= 0) {
        return null;
    }

    crm_ensure_schema($pdo);
    $stmt = $pdo->prepare('SELECT id, name, phone, email, company FROM clients WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $clientId]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$client) {
        unset($_SESSION['client_id']);
        return null;
    }

    return $client;
}

function client_require_user(PDO $pdo): array
{
    $client = client_auth_user($pdo);
    if (!$client) {
        header('Location: login.php');
        exit;
    }

    return $client;
}

function client_login(PDO $pdo, string $email, string $password): ?array
{
    crm_ensure_schema($pdo);
    $stmt = $pdo->prepare('SELECT id, name, phone, email, password_hash FROM clients WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => trim($email)]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client || !password_verify($password, (string) ($client['password_hash'] ?? ''))) {
        return null;
    }

    client_session_start();
    session_regenerate_id(true);
    $_SESSION['client_id'] = (int) $client['id'];

    unset($client['password_hash']);
    return $client;
}

function client_logout(): void
{
    client_session_start();
    unset($_SESSION['client_id']);
}
