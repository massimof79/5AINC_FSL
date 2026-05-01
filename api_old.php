<?php
/**
 * api.php — FSL Panel · API Layer AZIENDA
 *
 * Classe AziendeApi: incapsula tutte le operazioni CRUD sulla
 * tabella AZIENDA usando PDO con prepared statements.
 *
 * Sicurezza:
 *  - PDO con ATTR_EMULATE_PREPARES = false (native prepared)
 *  - ATTR_ERRMODE = EXCEPTION
 *  - Nessun dato esterno mai interpolato in SQL
 *  - Controllo unicità partita_iva lato DB + gestione eccezione
 *  - Logging degli errori DB senza esposizione al client
 *
 * @requires db_conn.php – deve restituire un oggetto PDO tramite
 *                         la funzione getDbConnection()
 */

declare(strict_types=1);

require_once __DIR__ . '/db_conn.php';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

requireLoginApi();


// ════════════════════════════════════════════════════════
// API Gruppo 1
// ════════════════════════════════════════════════════════

class AziendeApi
{
    private PDO $pdo;

    /**
     * Costruttore: ottiene la connessione PDO da db_conn.php.
     * db_conn.php deve esporre: function getDbConnection(): PDO
     *
     * @throws RuntimeException se la connessione fallisce
     */
    public function __construct()
    {
        try {
            $this->pdo = getDbConnection();

            // Assicura impostazioni di sicurezza PDO (ridondante ma esplicito)
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[FSL][API] Connessione DB fallita: ' . $e->getMessage());
            throw new RuntimeException('Impossibile connettersi al database.');
        }
    }

    // ════════════════════════════════════════════════════════
    // READ ALL
    // ════════════════════════════════════════════════════════

    /**
     * Restituisce tutte le aziende ordinate per ragione sociale.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT codice_azienda, ragione_sociale, partita_iva,
                    sede_legale, sede_operativa
             FROM AZIENDA
             ORDER BY ragione_sociale ASC'
        );
        return $stmt->fetchAll();
    }

    // ════════════════════════════════════════════════════════
    // READ ONE
    // ════════════════════════════════════════════════════════

    /**
     * Restituisce una singola azienda per ID.
     *
     * @param  int        $id
     * @return array<string, mixed>|null  null se non trovata
     */
    public function getOne(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT codice_azienda, ragione_sociale, partita_iva,
                    sede_legale, sede_operativa
             FROM AZIENDA
             WHERE codice_azienda = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ════════════════════════════════════════════════════════
    // CREATE
    // ════════════════════════════════════════════════════════

    /**
     * Crea una nuova azienda.
     *
     * @param  array<string, string> $data  Campi validati dal middleware
     * @return array<string, mixed>         Record appena creato
     * @throws RuntimeException             Se la P.IVA è già presente
     */
    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO AZIENDA (ragione_sociale, partita_iva, sede_legale, sede_operativa)
             VALUES (:ragione_sociale, :partita_iva, :sede_legale, :sede_operativa)'
        );

        try {
            $stmt->execute([
                ':ragione_sociale' => $data['ragione_sociale'],
                ':partita_iva'     => $data['partita_iva'],
                ':sede_legale'     => $data['sede_legale'],
                ':sede_operativa'  => $data['sede_operativa'],
            ]);
        } catch (PDOException $e) {
            // Codice 23000 = violazione constraint (es. UNIQUE su partita_iva)
            if ($e->getCode() === '23000') {
                throw new RuntimeException('Partita IVA già presente nel sistema.');
            }
            error_log('[FSL][API][CREATE] ' . $e->getMessage());
            throw new RuntimeException('Errore durante la creazione dell\'azienda.');
        }

        $newId = (int) $this->pdo->lastInsertId();
        return $this->getOne($newId) ?? ['codice_azienda' => $newId];
    }

    // ════════════════════════════════════════════════════════
    // UPDATE
    // ════════════════════════════════════════════════════════

    /**
     * Aggiorna un'azienda esistente.
     *
     * @param  int                   $id
     * @param  array<string, string> $data  Campi validati dal middleware
     * @return array<string, mixed>|false   Record aggiornato, o false se non trovato
     * @throws RuntimeException             Se la P.IVA è già usata da un'altra azienda
     */
    public function update(int $id, array $data): array|false
    {
        // Verifica esistenza prima di aggiornare
        if ($this->getOne($id) === null) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE AZIENDA
             SET ragione_sociale = :ragione_sociale,
                 partita_iva     = :partita_iva,
                 sede_legale     = :sede_legale,
                 sede_operativa  = :sede_operativa
             WHERE codice_azienda = :id'
        );

        try {
            $stmt->execute([
                ':ragione_sociale' => $data['ragione_sociale'],
                ':partita_iva'     => $data['partita_iva'],
                ':sede_legale'     => $data['sede_legale'],
                ':sede_operativa'  => $data['sede_operativa'],
                ':id'              => $id,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new RuntimeException('Partita IVA già associata a un\'altra azienda.');
            }
            error_log('[FSL][API][UPDATE] ' . $e->getMessage());
            throw new RuntimeException('Errore durante l\'aggiornamento dell\'azienda.');
        }

        return $this->getOne($id);
    }

    // ════════════════════════════════════════════════════════
    // DELETE
    // ════════════════════════════════════════════════════════

    /**
     * Elimina un'azienda per ID.
     *
     * ATTENZIONE: la tabella DISPONIBILITA ha FK con ON DELETE CASCADE
     * verso AZIENDA, quindi le disponibilità collegate verranno
     * eliminate automaticamente dal DB.
     * Valutare se aggiungere un controllo preventivo per informare
     * l'utente prima della cancellazione.
     *
     * @param  int  $id
     * @return bool  true se eliminata, false se non trovata
     */
    public function delete(int $id): bool
    {
        if ($this->getOne($id) === null) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'DELETE FROM AZIENDA WHERE codice_azienda = :id LIMIT 1'
        );

        try {
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('[FSL][API][DELETE] ' . $e->getMessage());
            throw new RuntimeException('Errore durante l\'eliminazione dell\'azienda.');
        }

        return $stmt->rowCount() > 0;
    }

    // ════════════════════════════════════════════════════════
    // UTILITY PRIVATA
    // ════════════════════════════════════════════════════════

    /**
     * Verifica se una partita IVA è già usata (opzionale, per messaggi user-friendly).
     */
    private function pivaExists(string $piva, ?int $excludeId = null): bool
    {
        $sql  = 'SELECT 1 FROM AZIENDA WHERE partita_iva = :piva';
        $params = [':piva' => $piva];

        if ($excludeId !== null) {
            $sql .= ' AND codice_azienda != :id';
            $params[':id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }
}



// ════════════════════════════════════════════════════════
// API Gruppo 2
// ════════════════════════════════════════════════════════
class Disponibilita Manager {
    private $pdo;

    public function __construct($db_config) {
        try {
            $this->pdo = new PDO(
                "mysql:host={$db_config['host']};dbname={$db_config['db']}", 
                $db_config['user'], 
                $db_config['pass']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            header('Content-Type: application/json', true, 500);
            die(json_encode(["error" => "Connessione fallita"]));
        }
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM disponibilita");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data, $ora_i, $ora_f) {
        $sql = "INSERT INTO disponibilita (data, ora_inizio, ora_fine) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data, $ora_i, $ora_f]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM disponibilita WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function linkAzienda($id_azienda, $id_disp) {
        $sql = "INSERT INTO azienda_disponibilità (id_azienda, id_disponibilità) VALUES (?, ?)";
        return $this->pdo->prepare($sql)->execute([$id_azienda, $id_disp]);
    }
}

// ════════════════════════════════════════════════════════
// API Gruppo 3
// ════════════════════════════════════════════════════════
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

        'disponibilita' => $pdo
            ->query('SELECT codice_disponibilita AS id,
                            CONCAT("Periodo: ", periodo_previsto, " - ", descrizione) AS label
                     FROM DISPONIBILITA ORDER BY data_inizio DESC')
            ->fetchAll(PDO::FETCH_ASSOC),
    };

    respond(true, $rows);
}

// ════════════════════════════════════════════════════════════
//  READ — lista con JOIN
// ════════════════════════════════════════════════════════════

function listEsperienze(PDO $pdo): never
{
    // Prima verifichiamo se la tabella ESPERIENZA esiste
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
            LEFT JOIN TUTOR_SCOLASTICO ts ON ts.codice_docente        = e.codice_docente
            LEFT JOIN TUTOR_AZIENDALE  ta ON ta.codice_tutor          = e.codice_tutor
            LEFT JOIN DISPONIBILITA     d ON d.codice_disponibilita   = e.codice_disponibilita
            ORDER BY e.codice_esperienza ASC /*Modifica tra ASC e DISC per l'ordine nella tabella*/
        SQL;
    
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        respond(true, $rows);
    } catch (PDOException $e) {
        error_log('[api_esperienze] listEsperienze: ' . $e->getMessage());
        
        // Estrai un messaggio più user-friendly
        $message = 'Errore nel caricamento dei dati.';
        if (strpos($e->getMessage(), 'Base table or view not found') !== false) {
            $message = 'Tabelle del database non trovate. Contattare l\'amministratore.';
        } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
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
    $body = parseRequestBody();
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
    // Verifica esistenza
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

/** Legge il body della richiesta come JSON. */
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

/** Valida e sanitizza i campi obbligatori. Ritorna array pulito. */
function validateFields(array $body): array
{
    $errors = [];

    $periodo        = trim((string) ($body['periodo_effettivo']    ?? ''));
    $orePreviste    = isset($body['numero_ore_previste'])    ? (int) $body['numero_ore_previste']    : null;
    $oreSvolte      = isset($body['numero_ore_svolte'])      ? (int) $body['numero_ore_svolte']      : null;
    $studenti       = isset($body['numero_studenti'])        ? (int) $body['numero_studenti']        : null;
    $docente        = isset($body['codice_docente'])         ? (int) $body['codice_docente']         : null;
    $disponibilita  = isset($body['codice_disponibilita'])   ? (int) $body['codice_disponibilita']   : null;
    $tutor          = isset($body['codice_tutor'])           ? (int) $body['codice_tutor']           : null;

    if ($periodo === '')      $errors[] = 'periodo_effettivo è obbligatorio.';
    if ($orePreviste === null || $orePreviste < 0) $errors[] = 'numero_ore_previste non valido.';
    if ($oreSvolte   === null || $oreSvolte   < 0) $errors[] = 'numero_ore_svolte non valido.';
    if ($studenti    === null || $studenti    < 0) $errors[] = 'numero_studenti non valido.';
    if (!$docente)       $errors[] = 'codice_docente è obbligatorio.';
    if (!$disponibilita) $errors[] = 'codice_disponibilita è obbligatorio.';
    if (!$tutor)         $errors[] = 'codice_tutor è obbligatorio.';

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

// ════════════════════════════════════════════════════════
// API Gruppo 4
// ════════════════════════════════════════════════════════

//sezione tutor aziendali
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

//sezione tutor scolastico
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


