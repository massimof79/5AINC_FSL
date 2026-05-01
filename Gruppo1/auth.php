<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function requireLoginPage(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireLoginApi(): void
{
    if (!isLoggedIn()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Sessione non valida. Effettua il login.',
            'data'    => null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int) $user['ID'];
    $_SESSION['username'] = (string) $user['username'];
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function verifyUserPassword(string $plainPassword, string $storedPassword): bool
{
    if (password_verify($plainPassword, $storedPassword)) {
        return true;
    }
    // Fallback per dati legacy in chiaro
    return hash_equals($storedPassword, $plainPassword);
}
