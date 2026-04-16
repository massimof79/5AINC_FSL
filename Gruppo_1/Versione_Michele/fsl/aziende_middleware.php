<?php
/**
 * aziende_middleware.php
 * Middleware REST per il modulo AZIENDA
 *
 * Responsabilità:
 *  - Validazione e sanitizzazione dell'input in ingresso
 *  - Protezione CSRF (token header-based per chiamate AJAX)
 *  - Gestione delle sessioni tramite sessioni.php
 *  - Inoltro sicuro verso api.php
 *  - Normalizzazione delle risposte JSON al frontend
 *
 * NON contiene logica di business né accesso diretto al DB.
 * Questi compiti sono delegati ad api.php e db_conn.php.
 *
 * @requires sessioni.php   – gestione sessioni e autenticazione
 * @requires api.php        – endpoint REST con accesso PDO al DB
 */

declare(strict_types=1);

// ── Security headers ──────────────────────────────────────────
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store, no-cache, must-revalidate');

// ── Dipendenze ────────────────────────────────────────────────
require_once __DIR__ . '/sessioni.php';   // avvia sessione, verifica login
require_once __DIR__ . '/api.php';        // espone la classe AziendeApi

// ── Solo richieste AJAX ───────────────────────────────────────
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    respondError(400, 'Richiesta non autorizzata.');
}

// ── Autenticazione: sessioni.php deve aver già verificato ─────
// Se l'utente non è autenticato, sessioni.php reindirizza o lancia
// un'eccezione. La riga seguente è una doppia verifica.
if (!isset($_SESSION['user_id'])) {
    respondError(401, 'Sessione non valida. Effettuare il login.');
}

// ── Lettura action dalla query string ────────────────────────
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$allowedActions = ['list', 'get', 'create', 'update', 'delete'];

if (!in_array($action, $allowedActions, true)) {
    respondError(400, 'Azione non riconosciuta.');
}

// ── Lettura body JSON (per POST/PUT) ─────────────────────────
$inputRaw  = file_get_contents('php://input');
$inputData = [];
if (!empty($inputRaw)) {
    $inputData = json_decode($inputRaw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        respondError(400, 'Payload JSON non valido.');
    }
}

// ── Istanzia API e delegato ───────────────────────────────────
try {
    $api = new AziendeApi(); // costruttore ottiene la connessione PDO tramite db_conn.php

    switch ($action) {

        // ── LIST: GET /aziende ─────────────────────────────────
        case 'list':
            requireMethod('GET');
            $result = $api->getAll();
            respondSuccess($result);
            break;

        // ── GET ONE: GET /aziende?action=get&id=N ──────────────
        case 'get':
            requireMethod('GET');
            $id = validateId($_GET['id'] ?? null);
            $result = $api->getOne($id);
            if ($result === null) {
                respondError(404, 'Azienda non trovata.');
            }
            respondSuccess($result);
            break;

        // ── CREATE: POST /aziende ──────────────────────────────
        case 'create':
            requireMethod('POST');
            $payload = sanitizeAziendaInput($inputData);
            $result  = $api->create($payload);
            respondSuccess($result, 201, 'Azienda creata con successo.');
            break;

        // ── UPDATE: PUT /aziende?action=update ────────────────
        case 'update':
            requireMethod('PUT');
            $id      = validateId($inputData['codice_azienda'] ?? null);
            $payload = sanitizeAziendaInput($inputData);
            $payload['codice_azienda'] = $id;
            $result  = $api->update($id, $payload);
            if ($result === false) {
                respondError(404, 'Azienda non trovata o nessuna modifica effettuata.');
            }
            respondSuccess($result, 200, 'Azienda aggiornata con successo.');
            break;

        // ── DELETE: DELETE /aziende?action=delete&id=N ─────────
        case 'delete':
            requireMethod('DELETE');
            $id = validateId($_GET['id'] ?? null);
            $result = $api->delete($id);
            if ($result === false) {
                respondError(404, 'Azienda non trovata.');
            }
            respondSuccess(null, 200, 'Azienda eliminata con successo.');
            break;
    }

} catch (InvalidArgumentException $e) {
    // Errori di validazione input
    respondError(422, $e->getMessage());
} catch (RuntimeException $e) {
    // Errori logica applicativa (es. P.IVA duplicata)
    respondError(409, $e->getMessage());
} catch (PDOException $e) {
    // Errori DB — non esponiamo dettagli interni
    error_log('[FSL][DB] ' . $e->getMessage());
    respondError(500, 'Errore interno del server. Riprovare più tardi.');
} catch (Throwable $e) {
    error_log('[FSL][ERR] ' . $e->getMessage());
    respondError(500, 'Errore imprevisto.');
}


// ════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ════════════════════════════════════════════════════════════

/**
 * Verifica che il metodo HTTP corrisponda a quello atteso.
 */
function requireMethod(string $expected): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    if (strtoupper($method) !== strtoupper($expected)) {
        respondError(405, "Metodo $method non consentito per questa operazione.");
    }
}

/**
 * Valida e restituisce un ID intero positivo.
 *
 * @throws InvalidArgumentException
 */
function validateId($raw): int {
    if ($raw === null || $raw === '') {
        throw new InvalidArgumentException('ID mancante.');
    }
    $id = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('ID non valido.');
    }
    return $id;
}

/**
 * Sanitizza e valida i campi in ingresso per AZIENDA.
 *
 * Regole:
 *  - ragione_sociale : stringa 2–100 caratteri, non vuota
 *  - partita_iva     : esattamente 11 cifre numeriche
 *  - sede_legale     : stringa 2–100 caratteri
 *  - sede_operativa  : stringa 2–100 caratteri
 *
 * @throws InvalidArgumentException se un campo è assente o malformato
 */
function sanitizeAziendaInput(array $data): array {
    $clean = [];

    // ragione_sociale
    $rs = isset($data['ragione_sociale']) ? trim(strip_tags($data['ragione_sociale'])) : '';
    if (strlen($rs) < 2 || strlen($rs) > 100) {
        throw new InvalidArgumentException('Ragione sociale: tra 2 e 100 caratteri.');
    }
    $clean['ragione_sociale'] = $rs;

    // partita_iva
    $piva = isset($data['partita_iva']) ? trim($data['partita_iva']) : '';
    if (!preg_match('/^\d{11}$/', $piva)) {
        throw new InvalidArgumentException('Partita IVA: deve essere esattamente 11 cifre numeriche.');
    }
    $clean['partita_iva'] = $piva;

    // sede_legale
    $sl = isset($data['sede_legale']) ? trim(strip_tags($data['sede_legale'])) : '';
    if (strlen($sl) < 2 || strlen($sl) > 100) {
        throw new InvalidArgumentException('Sede legale: tra 2 e 100 caratteri.');
    }
    $clean['sede_legale'] = $sl;

    // sede_operativa
    $so = isset($data['sede_operativa']) ? trim(strip_tags($data['sede_operativa'])) : '';
    if (strlen($so) < 2 || strlen($so) > 100) {
        throw new InvalidArgumentException('Sede operativa: tra 2 e 100 caratteri.');
    }
    $clean['sede_operativa'] = $so;

    return $clean;
}

/**
 * Risposta JSON di successo.
 */
function respondSuccess($data, int $status = 200, string $message = 'OK'): void {
    http_response_code($status);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

/**
 * Risposta JSON di errore.
 */
function respondError(int $status, string $message): void {
    http_response_code($status);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data'    => null,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}
