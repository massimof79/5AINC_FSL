<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sessione non valida. Effettua il login.',
        'data' => null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => '',
    'data' => [
        'user_id' => (int) $_SESSION['user_id'],
        'username' => (string) ($_SESSION['username'] ?? 'Utente'),
    ],
], JSON_UNESCAPED_UNICODE);
