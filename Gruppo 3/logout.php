<?php
/**
 * logout.php
 * Distrugge la sessione corrente e reindirizza al login.
 * Progetto: 5AINC_FSL
 */

declare(strict_types=1);

session_start();

// Svuota tutte le variabili di sessione
$_SESSION = [];

// Elimina il cookie di sessione dal browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Distrugge la sessione lato server
session_destroy();

// Reindirizza al login con messaggio di conferma via query string
header('Location: login.php?logout=1');
exit;
