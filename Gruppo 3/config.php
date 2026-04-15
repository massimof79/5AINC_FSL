<?php
/**
 * config.php
 * Connessione PDO al database condiviso del progetto.
 * Progetto: 5AINC_FSL
 *
 * Questo file espone una singola variabile: $pdo (istanza PDO).
 * Viene incluso tramite require_once da tutte le API dei gruppi.
 *
 * NON versionare questo file con credenziali reali su repository pubblici.
 */

declare(strict_types=1);

// ── Parametri di connessione ─────────────────────────────────
define('DB_HOST',    '127.0.0.1');
define('PORT',       '3307');
define('DB_NAME',    '5AINC_FSL');
define('DB_USER',    'root');       // modificare con l'utente del proprio ambiente
define('DB_PASS',    '');           // modificare con la password del proprio ambiente
define('DB_CHARSET', 'utf8mb4');

// ── DSN ──────────────────────────────────────────────────────
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    DB_HOST,
    PORT,
    DB_NAME,
    DB_CHARSET
);

// ── Opzioni PDO ───────────────────────────────────────────────
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // lancia PDOException sugli errori
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // fetch come array associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                    // prepared statements nativi
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];

// ── Istanza PDO ───────────────────────────────────────────────
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Non esporre dettagli interni: logga e termina
    error_log('[config] Connessione al database fallita: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Impossibile connettersi al database. Riprova più tardi.',
        'data'    => null,
    ]);
    exit;
}
