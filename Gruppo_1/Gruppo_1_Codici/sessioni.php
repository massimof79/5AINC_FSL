<?php
declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'secure' => false, // true se HTTPS
    'samesite' => 'Strict'
]);

session_start();

// login demo automatico (da sostituire con login reale)
if (!isset($_SESSION['utente'])) {
    $_SESSION['utente'] = "admin";
}