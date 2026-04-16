<?php
/**
 * API CRUD per tabella TUTOR_SCOLASTICO.
 */
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

requireLoginApi();

function respond(bool $success, mixed $data = null, string $message = '', int $httpCode = 200): never
{
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    match ($method) {
        'GET' => $id ? getTutor($pdo, $id) : listTutor($pdo),
        'POST' => createTutor($pdo),
        'PUT' => $id ? updateTutor($pdo, $id) : respond(false, null, 'ID mancante per PUT.', 400),
        'DELETE' => $id ? deleteTutor($pdo, $id) : respond(false, null, 'ID mancante per DELETE.', 400),
        default => respond(false, null, 'Metodo HTTP non supportato.', 405),
    };
} catch (PDOException $e) {
    error_log('[api_tutor_scolastici] PDOException: ' . $e->getMessage());
    respond(false, null, 'Errore del database. Riprova più tardi.', 500);
} catch (JsonException $e) {
    respond(false, null, 'Errore di encoding JSON.', 500);
}

function listTutor(PDO $pdo): never
{
    $rows = $pdo->query('SELECT codice_docente, nome, cognome, tipo, numero_studenti FROM TUTOR_SCOLASTICO ORDER BY codice_docente DESC')->fetchAll(PDO::FETCH_ASSOC);
    respond(true, $rows);
}

function getTutor(PDO $pdo, int $id): never
{
    $stmt = $pdo->prepare('SELECT codice_docente, nome, cognome, tipo, numero_studenti FROM TUTOR_SCOLASTICO WHERE codice_docente = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        respond(false, null, "Tutor scolastico con ID {$id} non trovato.", 404);
    }

    respond(true, $row);
}

function createTutor(PDO $pdo): never
{
    $fields = validateFields(parseRequestBody());

    $sql = 'INSERT INTO TUTOR_SCOLASTICO (nome, cognome, tipo, numero_studenti) VALUES (:nome, :cognome, :tipo, :numero_studenti)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $fields['nome'],
        ':cognome' => $fields['cognome'],
        ':tipo' => $fields['tipo'],
        ':numero_studenti' => $fields['numero_studenti'],
    ]);

    respond(true, ['codice_docente' => (int) $pdo->lastInsertId()], 'Tutor scolastico creato con successo.', 201);
}

function updateTutor(PDO $pdo, int $id): never
{
    ensureExists($pdo, $id);
    $fields = validateFields(parseRequestBody());

    $sql = 'UPDATE TUTOR_SCOLASTICO SET nome = :nome, cognome = :cognome, tipo = :tipo, numero_studenti = :numero_studenti WHERE codice_docente = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $fields['nome'],
        ':cognome' => $fields['cognome'],
        ':tipo' => $fields['tipo'],
        ':numero_studenti' => $fields['numero_studenti'],
        ':id' => $id,
    ]);

    respond(true, ['codice_docente' => $id], 'Tutor scolastico aggiornato con successo.');
}

function deleteTutor(PDO $pdo, int $id): never
{
    ensureExists($pdo, $id);
    $stmt = $pdo->prepare('DELETE FROM TUTOR_SCOLASTICO WHERE codice_docente = :id');
    $stmt->execute([':id' => $id]);

    respond(true, null, 'Tutor scolastico eliminato con successo.');
}

function ensureExists(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('SELECT codice_docente FROM TUTOR_SCOLASTICO WHERE codice_docente = :id');
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        respond(false, null, "Tutor scolastico con ID {$id} non trovato.", 404);
    }
}

function parseRequestBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
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
    $allowedTipo = ['dipartimento', 'area disciplinare'];

    $nome = trim((string) ($body['nome'] ?? ''));
    $cognome = trim((string) ($body['cognome'] ?? ''));
    $tipo = trim((string) ($body['tipo'] ?? ''));
    $numeroStudenti = isset($body['numero_studenti']) ? (int) $body['numero_studenti'] : null;

    if ($nome === '') $errors[] = 'nome è obbligatorio.';
    if ($cognome === '') $errors[] = 'cognome è obbligatorio.';
    if (!in_array($tipo, $allowedTipo, true)) $errors[] = 'tipo non valido.';
    if ($numeroStudenti === null || $numeroStudenti < 0) $errors[] = 'numero_studenti non valido.';

    if ($errors) {
        respond(false, ['errors' => $errors], implode(' | ', $errors), 422);
    }

    return [
        'nome' => $nome,
        'cognome' => $cognome,
        'tipo' => $tipo,
        'numero_studenti' => $numeroStudenti,
    ];
}
