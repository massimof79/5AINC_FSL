<?php
/**
 * db_conn.php — FSL Panel · Connessione PDO
 *
 * Espone SOLO la funzione getDbConnection().
 * NON chiama session_start() — la gestione sessione
 * è responsabilità esclusiva di sessioni_init.php.
 */

declare(strict_types=1);

define('DB_HOST',    'localhost');
define('DB_NAME',    '5ainc_fsl');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);

    return $pdo;
}
