<?php
/**
 * API CRUD per tabella TUTOR_AZIENDALE.
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
    error_log('[api_tutor_aziendali] PDOException: ' . $e->getMessage());
    respond(false, null, 'Errore del database. Riprova più tardi.', 500);
} catch (JsonException $e) {
    respond(false, null, 'Errore di encoding JSON.', 500);
}

function listTutor(PDO $pdo): never
{
    $rows = $pdo->query('SELECT codice_tutor, nome, cognome, ruolo, email FROM TUTOR_AZIENDALE ORDER BY codice_tutor DESC')->fetchAll(PDO::FETCH_ASSOC);
    respond(true, $rows);
}

function getTutor(PDO $pdo, int $id): never
{
    $stmt = $pdo->prepare('SELECT codice_tutor, nome, cognome, ruolo, email FROM TUTOR_AZIENDALE WHERE codice_tutor = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        respond(false, null, "Tutor aziendale con ID {$id} non trovato.", 404);
    }

    respond(true, $row);
}

function createTutor(PDO $pdo): never
{
    $fields = validateFields(parseRequestBody());

    $sql = 'INSERT INTO TUTOR_AZIENDALE (nome, cognome, ruolo, email) VALUES (:nome, :cognome, :ruolo, :email)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $fields['nome'],
        ':cognome' => $fields['cognome'],
        ':ruolo' => $fields['ruolo'],
        ':email' => $fields['email'],
    ]);

    respond(true, ['codice_tutor' => (int) $pdo->lastInsertId()], 'Tutor aziendale creato con successo.', 201);
}

function updateTutor(PDO $pdo, int $id): never
{
    ensureExists($pdo, $id);
    $fields = validateFields(parseRequestBody());

    $sql = 'UPDATE TUTOR_AZIENDALE SET nome = :nome, cognome = :cognome, ruolo = :ruolo, email = :email WHERE codice_tutor = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $fields['nome'],
        ':cognome' => $fields['cognome'],
        ':ruolo' => $fields['ruolo'],
        ':email' => $fields['email'],
        ':id' => $id,
    ]);

    respond(true, ['codice_tutor' => $id], 'Tutor aziendale aggiornato con successo.');
}

function deleteTutor(PDO $pdo, int $id): never
{
    ensureExists($pdo, $id);

    $checkRef = $pdo->prepare('SELECT COUNT(*) FROM ESPERIENZA WHERE codice_tutor = :id');
    $checkRef->execute([':id' => $id]);
    $references = (int) $checkRef->fetchColumn();
    if ($references > 0) {
        respond(
            false,
            ['references' => $references],
            'Impossibile eliminare: il tutor aziendale è associato a una o più esperienze.',
            409
        );
    }

    $stmt = $pdo->prepare('DELETE FROM TUTOR_AZIENDALE WHERE codice_tutor = :id');
    $stmt->execute([':id' => $id]);

    respond(true, null, 'Tutor aziendale eliminato con successo.');
}

function ensureExists(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('SELECT codice_tutor FROM TUTOR_AZIENDALE WHERE codice_tutor = :id');
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        respond(false, null, "Tutor aziendale con ID {$id} non trovato.", 404);
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

    $nome = trim((string) ($body['nome'] ?? ''));
    $cognome = trim((string) ($body['cognome'] ?? ''));
    $ruolo = trim((string) ($body['ruolo'] ?? ''));
    $email = trim((string) ($body['email'] ?? ''));

    if ($nome === '') $errors[] = 'nome è obbligatorio.';
    if ($cognome === '') $errors[] = 'cognome è obbligatorio.';
    if ($ruolo === '') $errors[] = 'ruolo è obbligatorio.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email non valida.';

    if ($errors) {
        respond(false, ['errors' => $errors], implode(' | ', $errors), 422);
    }

    return [
        'nome' => $nome,
        'cognome' => $cognome,
        'ruolo' => $ruolo,
        'email' => $email,
    ];
}
