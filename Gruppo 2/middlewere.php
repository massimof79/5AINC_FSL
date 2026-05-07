<?php
/**
 * middlewere.php — Gruppo 2 · Disponibilità
 *
 * Middleware leggero: delega interamente a DisponibilitaApi
 * già definita in api.php (flusso principale).
 *
 * URL: middlewere.php?id=<n>   (GET/DELETE singolo record)
 *      middlewere.php           (GET lista / POST crea)
 *
 * @requires config.php  – getDbConnection(): PDO
 * @requires auth.php    – requireLoginApi(), session_start()
 * @requires api.php     – classe DisponibilitaApi
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/api.php';

// Intestazioni JSON già impostate da api.php, ma le reimpostiamo
// per sicurezza nel caso questo file venisse chiamato direttamente.
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Autenticazione centralizzata (auth.php): risponde 401 e termina
// se la sessione non è valida.
requireLoginApi();

// Istanzia e smista la richiesta HTTP alla classe del flusso principale.
try {
    $api = new DisponibilitaApi();
    $api->handleRequest();
} catch (RuntimeException $e) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data'    => null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
