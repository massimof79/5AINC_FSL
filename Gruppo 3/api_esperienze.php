<?php
/**
 * api_esperienze.php
 * REST API per la gestione CRUD della tabella ESPERIENZA.
 * Progetto: 5AINC_FSL — Gruppo ESPERIENZA
 *
 * Endpoints:
 *   GET    /api_esperienze.php          → lista tutte le esperienze (con JOIN)
 *   GET    /api_esperienze.php?id=N     → singola esperienza
 *   POST   /api_esperienze.php          → crea una nuova esperienza
 *   PUT    /api_esperienze.php?id=N     → aggiorna un'esperienza esistente
 *   DELETE /api_esperienze.php?id=N     → elimina un'esperienza
 *
 *   GET    /api_esperienze.php?resource=tutor_scolastico   → lista tutor scolastici
 *   GET    /api_esperienze.php?resource=tutor_aziendale    → lista tutor aziendali
 *   GET    /api_esperienze.php?resource=disponibilita      → lista disponibilità
 */

declare(strict_types=1);

// ── Sessione e autenticazione (Gruppo 4) ─────────────────────
require_once __DIR__ . '/auth.php';    // isLoggedIn, requireLoginApi
require_once __DIR__ . '/config.php';  // $pdo

// ── Intestazioni JSON ────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Helper: risposta JSON standardizzata ─────────────────────
function respond(bool $success, mixed $data = null, string $message = '', int $httpCode = 200): never
{
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Verifica sessione attiva (tramite auth.php del Gruppo 4) ──
requireLoginApi();

/** @var PDO $pdo */

// ── Router ───────────────────────────────────────────────────
$method   = $_SERVER['REQUEST_METHOD'];
$id       = isset($_GET['id']) ? (int) $_GET['id'] : null;
$resource = $_GET['resource'] ?? null;

try {
    if ($resource !== null) {
        handleResource($pdo, $resource);
    }

    match ($method) {
        'GET'    => $id ? getEsperienza($pdo, $id) : listEsperienze($pdo),
        'POST'   => createEsperienza($pdo),
        'PUT'    => $id ? updateEsperienza($pdo, $id) : respond(false, null, 'ID mancante per PUT.', 400),
        'DELETE' => $id ? deleteEsperienza($pdo, $id) : respond(false, null, 'ID mancante per DELETE.', 400),
        default  => respond(false, null, 'Metodo HTTP non supportato.', 405),
    };
} catch (PDOException $e) {
    error_log('[api_esperienze] PDOException: ' . $e->getMessage());
    respond(false, null, 'Errore del database. Riprova più tardi.', 500);
} catch (JsonException $e) {
    respond(false, null, 'Errore di encoding JSON.', 500);
}

// ════════════════════════════════════════════════════════════
//  HANDLER RISORSE AUSILIARIE
// ════════════════════════════════════════════════════════════

function handleResource(PDO $pdo, string $resource): never
{
    $allowed = ['tutor_scolastico', 'tutor_aziendale', 'disponibilita'];

    if (!in_array($resource, $allowed, true)) {
        respond(false, null, 'Risorsa non valida.', 400);
    }

    $rows = match ($resource) {
        'tutor_scolastico' => $pdo
            ->query('SELECT codice_docente AS id, CONCAT(nome, " ", cognome) AS label
                     FROM TUTOR_SCOLASTICO ORDER BY cognome, nome')
            ->fetchAll(PDO::FETCH_ASSOC),

        'tutor_aziendale' => $pdo
            ->query('SELECT codice_tutor AS id, CONCAT(nome, " ", cognome) AS label
                     FROM TUTOR_AZIENDALE ORDER BY cognome, nome')
            ->fetchAll(PDO::FETCH_ASSOC),

        // BUG FIX: era ORDER BY data_inizio (inesistente) → corretto in periodo_previsto
        'disponibilita' => $pdo
            ->query('SELECT codice_disponibilita AS id,
                            CONCAT("Periodo: ", periodo_previsto, " - ", descrizione) AS label
                     FROM DISPONIBILITA ORDER BY periodo_previsto DESC')
            ->fetchAll(PDO::FETCH_ASSOC),
    };

    respond(true, $rows);
}

// ════════════════════════════════════════════════════════════
//  READ — lista con JOIN
// ════════════════════════════════════════════════════════════

function listEsperienze(PDO $pdo): never
{
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'ESPERIENZA'");
        if ($checkTable->rowCount() === 0) {
            respond(true, [], 'Nessuna tabella ESPERIENZA trovata. Creare prima le tabelle.');
        }

        $sql = <<<'SQL'
            SELECT
                e.codice_esperienza,
                e.periodo_effettivo,
                e.numero_ore_previste,
                e.numero_ore_svolte,
                e.numero_studenti,
                e.codice_docente,
                e.codice_disponibilita,
                e.codice_tutor,
                CONCAT(ts.nome, ' ', ts.cognome) AS nome_tutor_scolastico,
                CONCAT(ta.nome, ' ', ta.cognome) AS nome_tutor_aziendale,
                d.periodo_previsto AS data_disponibilita
            FROM ESPERIENZA e
            LEFT JOIN TUTOR_SCOLASTICO ts ON ts.codice_docente      = e.codice_docente
            LEFT JOIN TUTOR_AZIENDALE  ta ON ta.codice_tutor        = e.codice_tutor
            LEFT JOIN DISPONIBILITA     d ON d.codice_disponibilita = e.codice_disponibilita
            ORDER BY e.codice_esperienza ASC
        SQL;

        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        respond(true, $rows);

    } catch (PDOException $e) {
        error_log('[api_esperienze] listEsperienze: ' . $e->getMessage());

        $message = 'Errore nel caricamento dei dati.';
        if (str_contains($e->getMessage(), 'Base table or view not found')) {
            $message = 'Tabelle del database non trovate. Contattare l\'amministratore.';
        } elseif (str_contains($e->getMessage(), 'Unknown column')) {
            $message = 'Struttura del database incompleta. Verificare le colonne delle tabelle.';
        }

        respond(false, null, $message, 500);
    }
}

// ════════════════════════════════════════════════════════════
//  READ — singola
// ════════════════════════════════════════════════════════════

function getEsperienza(PDO $pdo, int $id): never
{
    $sql = <<<'SQL'
        SELECT
            e.codice_esperienza,
            e.periodo_effettivo,
            e.numero_ore_previste,
            e.numero_ore_svolte,
            e.numero_studenti,
            e.codice_docente,
            e.codice_disponibilita,
            e.codice_tutor
        FROM ESPERIENZA e
        WHERE e.codice_esperienza = :id
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        respond(false, null, "Esperienza con ID {$id} non trovata.", 404);
    }

    respond(true, $row);
}

// ════════════════════════════════════════════════════════════
//  CREATE
// ════════════════════════════════════════════════════════════

function createEsperienza(PDO $pdo): never
{
    $body   = parseRequestBody();
    $fields = validateFields($body);

    $sql = <<<'SQL'
        INSERT INTO ESPERIENZA
            (periodo_effettivo, numero_ore_previste, numero_ore_svolte,
             numero_studenti, codice_docente, codice_disponibilita, codice_tutor)
        VALUES
            (:periodo, :ore_previste, :ore_svolte,
             :studenti, :docente, :disponibilita, :tutor)
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':periodo'       => $fields['periodo_effettivo'],
        ':ore_previste'  => $fields['numero_ore_previste'],
        ':ore_svolte'    => $fields['numero_ore_svolte'],
        ':studenti'      => $fields['numero_studenti'],
        ':docente'       => $fields['codice_docente'],
        ':disponibilita' => $fields['codice_disponibilita'],
        ':tutor'         => $fields['codice_tutor'],
    ]);

    $newId = (int) $pdo->lastInsertId();
    respond(true, ['codice_esperienza' => $newId], 'Esperienza creata con successo.', 201);
}

// ════════════════════════════════════════════════════════════
//  UPDATE
// ════════════════════════════════════════════════════════════

function updateEsperienza(PDO $pdo, int $id): never
{
    $check = $pdo->prepare('SELECT codice_esperienza FROM ESPERIENZA WHERE codice_esperienza = :id');
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        respond(false, null, "Esperienza con ID {$id} non trovata.", 404);
    }

    $body   = parseRequestBody();
    $fields = validateFields($body);

    $sql = <<<'SQL'
        UPDATE ESPERIENZA SET
            periodo_effettivo    = :periodo,
            numero_ore_previste  = :ore_previste,
            numero_ore_svolte    = :ore_svolte,
            numero_studenti      = :studenti,
            codice_docente       = :docente,
            codice_disponibilita = :disponibilita,
            codice_tutor         = :tutor
        WHERE codice_esperienza  = :id
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':periodo'       => $fields['periodo_effettivo'],
        ':ore_previste'  => $fields['numero_ore_previste'],
        ':ore_svolte'    => $fields['numero_ore_svolte'],
        ':studenti'      => $fields['numero_studenti'],
        ':docente'       => $fields['codice_docente'],
        ':disponibilita' => $fields['codice_disponibilita'],
        ':tutor'         => $fields['codice_tutor'],
        ':id'            => $id,
    ]);

    respond(true, ['codice_esperienza' => $id], 'Esperienza aggiornata con successo.');
}

// ════════════════════════════════════════════════════════════
//  DELETE
// ════════════════════════════════════════════════════════════

function deleteEsperienza(PDO $pdo, int $id): never
{
    $check = $pdo->prepare('SELECT codice_esperienza FROM ESPERIENZA WHERE codice_esperienza = :id');
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        respond(false, null, "Esperienza con ID {$id} non trovata.", 404);
    }

    $stmt = $pdo->prepare('DELETE FROM ESPERIENZA WHERE codice_esperienza = :id');
    $stmt->execute([':id' => $id]);

    respond(true, null, 'Esperienza eliminata con successo.');
}

// ════════════════════════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════════════════════════

function parseRequestBody(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        respond(false, null, 'Body della richiesta vuoto.', 400);
    }

    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        respond(false, null, 'Body non è un oggetto JSON valido.', 400);
    }

    return $data;
}

function validateFields(array $body): array
{
    $errors = [];

    $periodo       = trim((string) ($body['periodo_effettivo']   ?? ''));
    $orePreviste   = isset($body['numero_ore_previste'])   ? (int) $body['numero_ore_previste']   : null;
    $oreSvolte     = isset($body['numero_ore_svolte'])     ? (int) $body['numero_ore_svolte']     : null;
    $studenti      = isset($body['numero_studenti'])       ? (int) $body['numero_studenti']       : null;
    $docente       = isset($body['codice_docente'])        ? (int) $body['codice_docente']        : null;
    $disponibilita = isset($body['codice_disponibilita'])  ? (int) $body['codice_disponibilita']  : null;
    $tutor         = isset($body['codice_tutor'])          ? (int) $body['codice_tutor']          : null;

    if ($periodo === '')                                $errors[] = 'periodo_effettivo è obbligatorio.';
    if ($orePreviste === null || $orePreviste < 0)     $errors[] = 'numero_ore_previste non valido.';
    if ($oreSvolte   === null || $oreSvolte   < 0)     $errors[] = 'numero_ore_svolte non valido.';
    if ($studenti    === null || $studenti    < 1)     $errors[] = 'numero_studenti non valido.';
    if (!$docente)                                     $errors[] = 'codice_docente è obbligatorio.';
    if (!$disponibilita)                               $errors[] = 'codice_disponibilita è obbligatorio.';
    if (!$tutor)                                       $errors[] = 'codice_tutor è obbligatorio.';

    if ($errors) {
        respond(false, ['errors' => $errors], implode(' | ', $errors), 422);
    }

    return [
        'periodo_effettivo'    => $periodo,
        'numero_ore_previste'  => $orePreviste,
        'numero_ore_svolte'    => $oreSvolte,
        'numero_studenti'      => $studenti,
        'codice_docente'       => $docente,
        'codice_disponibilita' => $disponibilita,
        'codice_tutor'         => $tutor,
    ];
}