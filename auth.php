<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_username'])) {
        header('Location: login.php');
        exit;
    }
}

function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    $saved = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !is_string($saved) ||
        $saved === '' ||
        !hash_equals($saved, $token)
    ) {
        http_response_code(403);
        exit('The request could not be verified. Reload the page and try again.');
    }
}