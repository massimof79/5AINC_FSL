<?php
/**
 * api.php — FSL Panel · API Layer unificata
 *
 * Questo file aggrega le API di tutti i gruppi in un unico entry point.
 * Ogni gruppo è incapsulato nella propria classe per evitare conflitti
 * di nomi (funzioni duplicate, router in conflitto, ecc.).
 *
 * Routing via query string:
 *   ?entity=aziende          → AziendeApi       (Gruppo 1 — invariato)
 *   ?entity=disponibilita    → DisponibilitaApi  (Gruppo 2 — refactored)
 *   ?entity=esperienze       → EsperienzeApi     (Gruppo 3 — refactored)
 *   ?entity=tutor_aziendali  → TutorAziendaliApi (Gruppo 4 — refactored)
 *   ?entity=tutor_scolastici → TutorScolasticiApi(Gruppo 4 — refactored)
 *
 * Sicurezza (comune a tutte le classi):
 *   - PDO con ATTR_EMULATE_PREPARES = false (native prepared statements)
 *   - ATTR_ERRMODE = EXCEPTION
 *   - Nessun dato esterno mai interpolato in SQL
 *   - Logging degli errori DB senza esposizione al client
 *
 * @requires config.php  – deve esporre getDbConnection(): PDO
 * @requires auth.php    – deve esporre requireLoginApi(): void
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php'; // getDbConnection(): PDO
require_once __DIR__ . '/auth.php';   // requireLoginApi()

// ── Intestazioni comuni ──────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Autenticazione ───────────────────────────────────────────
requireLoginApi();


// ════════════════════════════════════════════════════════════
// GRUPPO 1 — AziendeApi  (codice originale, nessuna modifica)
// ════════════════════════════════════════════════════════════

/**
 * Classe AziendeApi: incapsula tutte le operazioni CRUD sulla
 * tabella azienda usando PDO con prepared statements.
 */
class AziendeApi
{
    private PDO $pdo;

    /**
     * Costruttore: ottiene la connessione PDO da config.php.
     *
     * @throws RuntimeException se la connessione fallisce
     */
    public function __construct()
    {
        try {
            $this->pdo = getDbConnection();
            // Assicura impostazioni di sicurezza PDO (ridondante ma esplicito)
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[FSL][API] Connessione DB fallita: ' . $e->getMessage());
            throw new RuntimeException('Impossibile connettersi al database.');
        }
    }

    // ── READ ALL ─────────────────────────────────────────────

    /** @return array<int, array<string, mixed>> */
    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT codice_azienda, ragione_sociale, partita_iva,
                    sede_legale, sede_operativa
             FROM azienda
             ORDER BY ragione_sociale ASC'
        );
        return $stmt->fetchAll();
    }

    // ── READ ONE ─────────────────────────────────────────────

    /** @return array<string, mixed>|null null se non trovata */
    public function getOne(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT codice_azienda, ragione_sociale, partita_iva,
                    sede_legale, sede_operativa
             FROM azienda
             WHERE codice_azienda = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── CREATE ───────────────────────────────────────────────

    /**
     * @param array<string, string> $data Campi validati dal middleware
     * @return array<string, mixed> Record appena creato
     * @throws RuntimeException Se la P.IVA è già presente
     */
    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO azienda (ragione_sociale, partita_iva, sede_legale, sede_operativa)
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
            if ($e->getCode() === '23000') {
                throw new RuntimeException('Partita IVA già presente nel sistema.');
            }
            error_log('[FSL][API][CREATE] ' . $e->getMessage());
            throw new RuntimeException('Errore durante la creazione dell\'azienda.');
        }
        $newId = (int) $this->pdo->lastInsertId();
        return $this->getOne($newId) ?? ['codice_azienda' => $newId];
    }

    // ── UPDATE ───────────────────────────────────────────────

    /**
     * @param int $id
     * @param array<string, string> $data Campi validati dal middleware
     * @return array<string, mixed>|false Record aggiornato, o false se non trovato
     * @throws RuntimeException Se la P.IVA è già usata da un'altra azienda
     */
    public function update(int $id, array $data): array|false
    {
        if ($this->getOne($id) === null) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'UPDATE azienda
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

    // ── DELETE ───────────────────────────────────────────────

    /**
     * ATTENZIONE: la tabella DISPONIBILITA ha FK con ON DELETE CASCADE
     * verso azienda, quindi le disponibilità collegate verranno eliminate
     * automaticamente dal DB.
     *
     * @param int $id
     * @return bool true se eliminata, false se non trovata
     */
    public function delete(int $id): bool
    {
        if ($this->getOne($id) === null) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'DELETE FROM azienda WHERE codice_azienda = :id LIMIT 1'
        );
        try {
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('[FSL][API][DELETE] ' . $e->getMessage());
            throw new RuntimeException('Errore durante l\'eliminazione dell\'azienda.');
        }
        return $stmt->rowCount() > 0;
    }

    // ── UTILITY PRIVATA ──────────────────────────────────────

    /** Verifica se una partita IVA è già usata (per messaggi user-friendly). */
    private function pivaExists(string $piva, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT 1 FROM azienda WHERE partita_iva = :piva';
        $params = [':piva' => $piva];
        if ($excludeId !== null) {
            $sql .= ' AND codice_azienda != :id';
            $params[':id'] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    // ── ROUTER HTTP ──────────────────────────────────────────

    /** Gestisce la richiesta HTTP e invia la risposta JSON. */
    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

        try {
            switch ($method) {
                case 'GET':
                    $data = $id ? $this->getOne($id) : $this->getAll();
                    if ($id && $data === null) {
                        $this->respond(false, null, "Azienda con ID {$id} non trovata.", 404);
                    }
                    $this->respond(true, $data);
                    break;

                case 'POST':
                    $body = $this->parseBody();
                    $this->respond(true, $this->create($body), 'Azienda creata con successo.', 201);
                    break;

                case 'PUT':
                    if (!$id) $this->respond(false, null, 'ID mancante per PUT.', 400);
                    $body   = $this->parseBody();
                    $result = $this->update($id, $body);
                    if ($result === false) $this->respond(false, null, "Azienda con ID {$id} non trovata.", 404);
                    $this->respond(true, $result, 'Azienda aggiornata con successo.');
                    break;

                case 'DELETE':
                    if (!$id) $this->respond(false, null, 'ID mancante per DELETE.', 400);
                    $ok = $this->delete($id);
                    if (!$ok) $this->respond(false, null, "Azienda con ID {$id} non trovata.", 404);
                    $this->respond(true, null, 'Azienda eliminata con successo.');
                    break;

                default:
                    $this->respond(false, null, 'Metodo HTTP non supportato.', 405);
            }
        } catch (RuntimeException $e) {
            $this->respond(false, null, $e->getMessage(), 400);
        } catch (PDOException $e) {
            error_log('[FSL][AziendeApi] PDOException: ' . $e->getMessage());
            $this->respond(false, null, 'Errore del database. Riprova più tardi.', 500);
        }
    }

    private function respond(bool $success, mixed $data, string $message = '', int $code = 200): never
    {
        http_response_code($code);
        echo json_encode(
            ['success' => $success, 'message' => $message, 'data' => $data],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            $this->respond(false, null, 'Body della richiesta vuoto.', 400);
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            $this->respond(false, null, 'Body non è un oggetto JSON valido.', 400);
        }
        return $data;
    }
}


// ════════════════════════════════════════════════════════════
// GRUPPO 2 — DisponibilitaApi  (refactored da classe con bug)
// ════════════════════════════════════════════════════════════

/**
 * Gestione CRUD della tabella DISPONIBILITA e della tabella
 * di associazione azienda_disponibilita.
 *
 * Fix applicati rispetto all'originale:
 *   - Nome classe corretto (era "Disponibilita Manager" con spazio → errore di sintassi)
 *   - Connessione DB centralizzata tramite getDbConnection()
 *   - Aggiunto router HTTP + respond() come metodi privati
 *   - Aggiunta gestione errori PDO uniforme
 */
class DisponibilitaApi
{
    private PDO $pdo;

    public function __construct()
    {
        try {
            $this->pdo = getDbConnection();
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[FSL][DisponibilitaApi] Connessione DB fallita: ' . $e->getMessage());
            throw new RuntimeException('Impossibile connettersi al database.');
        }
    }

    // ── READ ALL ─────────────────────────────────────────────

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM disponibilita');
        return $stmt->fetchAll();
    }

    // ── READ ONE ─────────────────────────────────────────────

    public function getOne(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM disponibilita WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── CREATE ───────────────────────────────────────────────

    public function create(string $data, string $oraInizio, string $oraFine): bool
    {
        $sql  = 'INSERT INTO disponibilita (data, ora_inizio, ora_fine) VALUES (:data, :ora_i, :ora_f)';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':data' => $data, ':ora_i' => $oraInizio, ':ora_f' => $oraFine]);
    }

    // ── DELETE ───────────────────────────────────────────────

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM disponibilita WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    // ── LINK AZIENDA ─────────────────────────────────────────

    public function linkAzienda(int $idAzienda, int $idDisp): bool
    {
        $sql  = 'INSERT INTO azienda_disponibilita (id_azienda, id_disponibilita) VALUES (:id_az, :id_d)';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id_az' => $idAzienda, ':id_d' => $idDisp]);
    }

    // ── ROUTER HTTP ──────────────────────────────────────────

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

        try {
            switch ($method) {
                case 'GET':
                    $data = $id ? $this->getOne($id) : $this->getAll();
                    if ($id && $data === null) {
                        $this->respond(false, null, "Disponibilità con ID {$id} non trovata.", 404);
                    }
                    $this->respond(true, $data);
                    break;

                case 'POST':
                    $body      = $this->parseBody();
                    $action    = $body['action'] ?? 'create';

                    if ($action === 'link') {
                        // Collegamento azienda ↔ disponibilità
                        $idAz   = isset($body['id_azienda'])     ? (int) $body['id_azienda']     : null;
                        $idDisp = isset($body['id_disponibilita']) ? (int) $body['id_disponibilita'] : null;
                        if (!$idAz || !$idDisp) {
                            $this->respond(false, null, 'id_azienda e id_disponibilita sono obbligatori.', 422);
                        }
                        $this->linkAzienda($idAz, $idDisp);
                        $this->respond(true, null, 'Collegamento azienda-disponibilità creato.', 201);
                    } else {
                        // Creazione disponibilità
                        $data     = trim((string) ($body['data']       ?? ''));
                        $oraI     = trim((string) ($body['ora_inizio'] ?? ''));
                        $oraF     = trim((string) ($body['ora_fine']   ?? ''));
                        $errors   = [];
                        if ($data === '') $errors[] = 'data è obbligatoria.';
                        if ($oraI === '') $errors[] = 'ora_inizio è obbligatoria.';
                        if ($oraF === '') $errors[] = 'ora_fine è obbligatoria.';
                        if ($errors) $this->respond(false, ['errors' => $errors], implode(' | ', $errors), 422);
                        $this->create($data, $oraI, $oraF);
                        $newId = (int) $this->pdo->lastInsertId();
                        $this->respond(true, ['id' => $newId], 'Disponibilità creata con successo.', 201);
                    }
                    break;

                case 'DELETE':
                    if (!$id) $this->respond(false, null, 'ID mancante per DELETE.', 400);
                    if ($this->getOne($id) === null) {
                        $this->respond(false, null, "Disponibilità con ID {$id} non trovata.", 404);
                    }
                    $this->delete($id);
                    $this->respond(true, null, 'Disponibilità eliminata con successo.');
                    break;

                default:
                    $this->respond(false, null, 'Metodo HTTP non supportato.', 405);
            }
        } catch (PDOException $e) {
            error_log('[FSL][DisponibilitaApi] PDOException: ' . $e->getMessage());
            $this->respond(false, null, 'Errore del database. Riprova più tardi.', 500);
        }
    }

    private function respond(bool $success, mixed $data, string $message = '', int $code = 200): never
    {
        http_response_code($code);
        echo json_encode(
            ['success' => $success, 'message' => $message, 'data' => $data],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            $this->respond(false, null, 'Body della richiesta vuoto.', 400);
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            $this->respond(false, null, 'Body non è un oggetto JSON valido.', 400);
        }
        return $data;
    }
}


// ════════════════════════════════════════════════════════════
// GRUPPO 3 — EsperienzeApi  (refactored da funzioni globali)
// ════════════════════════════════════════════════════════════

/**
 * REST API per la gestione CRUD della tabella ESPERIENZA.
 *
 * Endpoints (via ?entity=esperienze):
 *   GET    ?entity=esperienze              → lista tutte le esperienze (con JOIN)
 *   GET    ?entity=esperienze&id=N         → singola esperienza
 *   GET    ?entity=esperienze&resource=X   → risorse ausiliarie (tutor, disponibilità)
 *   POST   ?entity=esperienze              → crea una nuova esperienza
 *   PUT    ?entity=esperienze&id=N         → aggiorna un'esperienza esistente
 *   DELETE ?entity=esperienze&id=N         → elimina un'esperienza
 *
 * Fix applicati rispetto all'originale:
 *   - Refactoring da funzioni globali a metodi di classe
 *   - Connessione DB centralizzata tramite getDbConnection()
 *   - respond() e parseRequestBody() diventano metodi privati
 */
class EsperienzeApi
{
    private PDO $pdo;

    public function __construct()
    {
        try {
            $this->pdo = getDbConnection();
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[FSL][EsperienzeApi] Connessione DB fallita: ' . $e->getMessage());
            throw new RuntimeException('Impossibile connettersi al database.');
        }
    }

    // ── ROUTER HTTP ──────────────────────────────────────────

    public function handleRequest(): void
    {
        $method   = $_SERVER['REQUEST_METHOD'];
        $id       = isset($_GET['id'])       ? (int)    $_GET['id']       : null;
        $resource = $_GET['resource'] ?? null;

        try {
            if ($resource !== null) {
                $this->handleResource($resource);
            }

            switch ($method) {
                case 'GET':
                    $id ? $this->getEsperienza($id) : $this->listEsperienze();
                    break;
                case 'POST':
                    $this->createEsperienza();
                    break;
                case 'PUT':
                    $id
                        ? $this->updateEsperienza($id)
                        : $this->respond(false, null, 'ID mancante per PUT.', 400);
                    break;
                case 'DELETE':
                    $id
                        ? $this->deleteEsperienza($id)
                        : $this->respond(false, null, 'ID mancante per DELETE.', 400);
                    break;
                default:
                    $this->respond(false, null, 'Metodo HTTP non supportato.', 405);
            }
        } catch (PDOException $e) {
            error_log('[FSL][EsperienzeApi] PDOException: ' . $e->getMessage());
            $this->respond(false, null, 'Errore del database. Riprova più tardi.', 500);
        } catch (JsonException $e) {
            $this->respond(false, null, 'Errore di encoding JSON.', 500);
        }
    }

    // ── RISORSE AUSILIARIE ───────────────────────────────────

    private function handleResource(string $resource): never
    {
        $allowed = ['tutor_scolastico', 'tutor_aziendale', 'disponibilita'];
        if (!in_array($resource, $allowed, true)) {
            $this->respond(false, null, 'Risorsa non valida.', 400);
        }

        $rows = match ($resource) {
            'tutor_scolastico' => $this->pdo
                ->query('SELECT codice_docente AS id, CONCAT(nome, " ", cognome) AS label
                          FROM TUTOR_SCOLASTICO ORDER BY cognome, nome')
                ->fetchAll(),
            'tutor_aziendale' => $this->pdo
                ->query('SELECT codice_tutor AS id, CONCAT(nome, " ", cognome) AS label
                          FROM TUTOR_AZIENDALE ORDER BY cognome, nome')
                ->fetchAll(),
            // BUG FIX originale: era ORDER BY data_inizio (inesistente) → corretto in periodo_previsto
            'disponibilita' => $this->pdo
                ->query('SELECT codice_disponibilita AS id,
                                CONCAT("Periodo: ", periodo_previsto, " - ", descrizione) AS label
                          FROM DISPONIBILITA ORDER BY periodo_previsto DESC')
                ->fetchAll(),
        };

        $this->respond(true, $rows);
    }

    // ── READ ALL ─────────────────────────────────────────────

    private function listEsperienze(): never
    {
        try {
            $checkTable = $this->pdo->query("SHOW TABLES LIKE 'ESPERIENZA'");
            if ($checkTable->rowCount() === 0) {
                $this->respond(true, [], 'Nessuna tabella ESPERIENZA trovata. Creare prima le tabelle.');
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
                LEFT JOIN TUTOR_SCOLASTICO ts ON ts.codice_docente = e.codice_docente
                LEFT JOIN TUTOR_AZIENDALE  ta ON ta.codice_tutor   = e.codice_tutor
                LEFT JOIN DISPONIBILITA    d  ON d.codice_disponibilita = e.codice_disponibilita
                ORDER BY e.codice_esperienza ASC
            SQL;

            $rows = $this->pdo->query($sql)->fetchAll();
            $this->respond(true, $rows);
        } catch (PDOException $e) {
            error_log('[FSL][EsperienzeApi] listEsperienze: ' . $e->getMessage());
            $message = 'Errore nel caricamento dei dati.';
            if (str_contains($e->getMessage(), 'Base table or view not found')) {
                $message = 'Tabelle del database non trovate. Contattare l\'amministratore.';
            } elseif (str_contains($e->getMessage(), 'Unknown column')) {
                $message = 'Struttura del database incompleta. Verificare le colonne delle tabelle.';
            }
            $this->respond(false, null, $message, 500);
        }
    }

    // ── READ ONE ─────────────────────────────────────────────

    private function getEsperienza(int $id): never
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

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            $this->respond(false, null, "Esperienza con ID {$id} non trovata.", 404);
        }
        $this->respond(true, $row);
    }

    // ── CREATE ───────────────────────────────────────────────

    private function createEsperienza(): never
    {
        $body   = $this->parseBody();
        $fields = $this->validateFields($body);

        $sql = <<<'SQL'
            INSERT INTO ESPERIENZA
                (periodo_effettivo, numero_ore_previste, numero_ore_svolte,
                 numero_studenti, codice_docente, codice_disponibilita, codice_tutor)
            VALUES
                (:periodo, :ore_previste, :ore_svolte,
                 :studenti, :docente, :disponibilita, :tutor)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':periodo'       => $fields['periodo_effettivo'],
            ':ore_previste'  => $fields['numero_ore_previste'],
            ':ore_svolte'    => $fields['numero_ore_svolte'],
            ':studenti'      => $fields['numero_studenti'],
            ':docente'       => $fields['codice_docente'],
            ':disponibilita' => $fields['codice_disponibilita'],
            ':tutor'         => $fields['codice_tutor'],
        ]);

        $newId = (int) $this->pdo->lastInsertId();
        $this->respond(true, ['codice_esperienza' => $newId], 'Esperienza creata con successo.', 201);
    }

    // ── UPDATE ───────────────────────────────────────────────

    private function updateEsperienza(int $id): never
    {
        $check = $this->pdo->prepare('SELECT codice_esperienza FROM ESPERIENZA WHERE codice_esperienza = :id');
        $check->execute([':id' => $id]);
        if (!$check->fetch()) {
            $this->respond(false, null, "Esperienza con ID {$id} non trovata.", 404);
        }

        $body   = $this->parseBody();
        $fields = $this->validateFields($body);

        $sql = <<<'SQL'
            UPDATE ESPERIENZA SET
                periodo_effettivo   = :periodo,
                numero_ore_previste = :ore_previste,
                numero_ore_svolte   = :ore_svolte,
                numero_studenti     = :studenti,
                codice_docente      = :docente,
                codice_disponibilita = :disponibilita,
                codice_tutor        = :tutor
            WHERE codice_esperienza = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
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

        $this->respond(true, ['codice_esperienza' => $id], 'Esperienza aggiornata con successo.');
    }

    // ── DELETE ───────────────────────────────────────────────

    private function deleteEsperienza(int $id): never
    {
        $check = $this->pdo->prepare('SELECT codice_esperienza FROM ESPERIENZA WHERE codice_esperienza = :id');
        $check->execute([':id' => $id]);
        if (!$check->fetch()) {
            $this->respond(false, null, "Esperienza con ID {$id} non trovata.", 404);
        }

        $stmt = $this->pdo->prepare('DELETE FROM ESPERIENZA WHERE codice_esperienza = :id');
        $stmt->execute([':id' => $id]);
        $this->respond(true, null, 'Esperienza eliminata con successo.');
    }

    // ── HELPERS PRIVATI ──────────────────────────────────────

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            $this->respond(false, null, 'Body della richiesta vuoto.', 400);
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            $this->respond(false, null, 'Body non è un oggetto JSON valido.', 400);
        }
        return $data;
    }

    private function validateFields(array $body): array
    {
        $errors = [];

        $periodo      = trim((string) ($body['periodo_effettivo']    ?? ''));
        $orePreviste  = isset($body['numero_ore_previste'])  ? (int) $body['numero_ore_previste']  : null;
        $oreSvolte    = isset($body['numero_ore_svolte'])    ? (int) $body['numero_ore_svolte']    : null;
        $studenti     = isset($body['numero_studenti'])      ? (int) $body['numero_studenti']      : null;
        $docente      = isset($body['codice_docente'])       ? (int) $body['codice_docente']       : null;
        $disponibilita = isset($body['codice_disponibilita']) ? (int) $body['codice_disponibilita'] : null;
        $tutor        = isset($body['codice_tutor'])         ? (int) $body['codice_tutor']         : null;

        if ($periodo === '')                         $errors[] = 'periodo_effettivo è obbligatorio.';
        if ($orePreviste === null || $orePreviste < 0) $errors[] = 'numero_ore_previste non valido.';
        if ($oreSvolte   === null || $oreSvolte < 0)   $errors[] = 'numero_ore_svolte non valido.';
        if ($studenti    === null || $studenti < 1)    $errors[] = 'numero_studenti non valido.';
        if (!$docente)                               $errors[] = 'codice_docente è obbligatorio.';
        if (!$disponibilita)                         $errors[] = 'codice_disponibilita è obbligatorio.';
        if (!$tutor)                                 $errors[] = 'codice_tutor è obbligatorio.';

        if ($errors) {
            $this->respond(false, ['errors' => $errors], implode(' | ', $errors), 422);
        }

        return [
            'periodo_effettivo'   => $periodo,
            'numero_ore_previste' => $orePreviste,
            'numero_ore_svolte'   => $oreSvolte,
            'numero_studenti'     => $studenti,
            'codice_docente'      => $docente,
            'codice_disponibilita' => $disponibilita,
            'codice_tutor'        => $tutor,
        ];
    }

    private function respond(bool $success, mixed $data, string $message = '', int $code = 200): never
    {
        http_response_code($code);
        echo json_encode(
            ['success' => $success, 'message' => $message, 'data' => $data],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}


// ════════════════════════════════════════════════════════════
// GRUPPO 4a — TutorAziendaliApi  (refactored da funzioni globali)
// ════════════════════════════════════════════════════════════

/**
 * REST API per la gestione CRUD della tabella TUTOR_AZIENDALE.
 *
 * Fix applicati:
 *   - Refactoring da funzioni globali a metodi di classe
 *   - Connessione DB centralizzata tramite getDbConnection()
 *   - respond(), parseRequestBody(), validateFields(), ensureExists()
 *     diventano metodi privati → nessun conflitto con le altre API
 */
class TutorAziendaliApi
{
    private PDO $pdo;

    public function __construct()
    {
        try {
            $this->pdo = getDbConnection();
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[FSL][TutorAziendaliApi] Connessione DB fallita: ' . $e->getMessage());
            throw new RuntimeException('Impossibile connettersi al database.');
        }
    }

    // ── ROUTER HTTP ──────────────────────────────────────────

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

        try {
            switch ($method) {
                case 'GET':
                    $id ? $this->getTutor($id) : $this->listTutor();
                    break;
                case 'POST':
                    $this->createTutor();
                    break;
                case 'PUT':
                    $id
                        ? $this->updateTutor($id)
                        : $this->respond(false, null, 'ID mancante per PUT.', 400);
                    break;
                case 'DELETE':
                    $id
                        ? $this->deleteTutor($id)
                        : $this->respond(false, null, 'ID mancante per DELETE.', 400);
                    break;
                default:
                    $this->respond(false, null, 'Metodo HTTP non supportato.', 405);
            }
        } catch (PDOException $e) {
            error_log('[FSL][TutorAziendaliApi] PDOException: ' . $e->getMessage());
            $this->respond(false, null, 'Errore del database. Riprova più tardi.', 500);
        } catch (JsonException $e) {
            $this->respond(false, null, 'Errore di encoding JSON.', 500);
        }
    }

    // ── READ ALL ─────────────────────────────────────────────

    private function listTutor(): never
    {
        $rows = $this->pdo
            ->query('SELECT codice_tutor, nome, cognome, ruolo, email
                     FROM TUTOR_AZIENDALE ORDER BY codice_tutor DESC')
            ->fetchAll();
        $this->respond(true, $rows);
    }

    // ── READ ONE ─────────────────────────────────────────────

    private function getTutor(int $id): never
    {
        $stmt = $this->pdo->prepare(
            'SELECT codice_tutor, nome, cognome, ruolo, email
             FROM TUTOR_AZIENDALE WHERE codice_tutor = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            $this->respond(false, null, "Tutor aziendale con ID {$id} non trovato.", 404);
        }
        $this->respond(true, $row);
    }

    // ── CREATE ───────────────────────────────────────────────

    private function createTutor(): never
    {
        $fields = $this->validateFields($this->parseBody());
        $stmt = $this->pdo->prepare(
            'INSERT INTO TUTOR_AZIENDALE (nome, cognome, ruolo, email)
             VALUES (:nome, :cognome, :ruolo, :email)'
        );
        $stmt->execute([
            ':nome'    => $fields['nome'],
            ':cognome' => $fields['cognome'],
            ':ruolo'   => $fields['ruolo'],
            ':email'   => $fields['email'],
        ]);
        $this->respond(true, ['codice_tutor' => (int) $this->pdo->lastInsertId()], 'Tutor aziendale creato con successo.', 201);
    }

    // ── UPDATE ───────────────────────────────────────────────

    private function updateTutor(int $id): never
    {
        $this->ensureExists($id);
        $fields = $this->validateFields($this->parseBody());
        $stmt = $this->pdo->prepare(
            'UPDATE TUTOR_AZIENDALE
             SET nome = :nome, cognome = :cognome, ruolo = :ruolo, email = :email
             WHERE codice_tutor = :id'
        );
        $stmt->execute([
            ':nome'    => $fields['nome'],
            ':cognome' => $fields['cognome'],
            ':ruolo'   => $fields['ruolo'],
            ':email'   => $fields['email'],
            ':id'      => $id,
        ]);
        $this->respond(true, ['codice_tutor' => $id], 'Tutor aziendale aggiornato con successo.');
    }

    // ── DELETE ───────────────────────────────────────────────

    private function deleteTutor(int $id): never
    {
        $this->ensureExists($id);

        // Verifica riferimenti in ESPERIENZA
        $checkRef = $this->pdo->prepare('SELECT COUNT(*) FROM ESPERIENZA WHERE codice_tutor = :id');
        $checkRef->execute([':id' => $id]);
        $references = (int) $checkRef->fetchColumn();
        if ($references > 0) {
            $this->respond(
                false,
                ['references' => $references],
                'Impossibile eliminare: il tutor aziendale è associato a una o più esperienze.',
                409
            );
        }

        $stmt = $this->pdo->prepare('DELETE FROM TUTOR_AZIENDALE WHERE codice_tutor = :id');
        $stmt->execute([':id' => $id]);
        $this->respond(true, null, 'Tutor aziendale eliminato con successo.');
    }

    // ── HELPERS PRIVATI ──────────────────────────────────────

    private function ensureExists(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT codice_tutor FROM TUTOR_AZIENDALE WHERE codice_tutor = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            $this->respond(false, null, "Tutor aziendale con ID {$id} non trovato.", 404);
        }
    }

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            $this->respond(false, null, 'Body della richiesta vuoto.', 400);
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            $this->respond(false, null, 'Body non è un oggetto JSON valido.', 400);
        }
        return $data;
    }

    private function validateFields(array $body): array
    {
        $errors  = [];
        $nome    = trim((string) ($body['nome']    ?? ''));
        $cognome = trim((string) ($body['cognome'] ?? ''));
        $ruolo   = trim((string) ($body['ruolo']   ?? ''));
        $email   = trim((string) ($body['email']   ?? ''));

        if ($nome    === '') $errors[] = 'nome è obbligatorio.';
        if ($cognome === '') $errors[] = 'cognome è obbligatorio.';
        if ($ruolo   === '') $errors[] = 'ruolo è obbligatorio.';
        if ($email   === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email non valida.';

        if ($errors) {
            $this->respond(false, ['errors' => $errors], implode(' | ', $errors), 422);
        }
        return ['nome' => $nome, 'cognome' => $cognome, 'ruolo' => $ruolo, 'email' => $email];
    }

    private function respond(bool $success, mixed $data, string $message = '', int $code = 200): never
    {
        http_response_code($code);
        echo json_encode(
            ['success' => $success, 'message' => $message, 'data' => $data],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}


// ════════════════════════════════════════════════════════════
// GRUPPO 4b — TutorScolasticiApi  (refactored da funzioni globali)
// ════════════════════════════════════════════════════════════

/**
 * REST API per la gestione CRUD della tabella TUTOR_SCOLASTICO.
 *
 * Fix applicati:
 *   - Refactoring da funzioni globali a metodi di classe
 *   - Connessione DB centralizzata tramite getDbConnection()
 */
class TutorScolasticiApi
{
    private PDO $pdo;

    public function __construct()
    {
        try {
            $this->pdo = getDbConnection();
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[FSL][TutorScolasticiApi] Connessione DB fallita: ' . $e->getMessage());
            throw new RuntimeException('Impossibile connettersi al database.');
        }
    }

    // ── ROUTER HTTP ──────────────────────────────────────────

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

        try {
            switch ($method) {
                case 'GET':
                    $id ? $this->getTutor($id) : $this->listTutor();
                    break;
                case 'POST':
                    $this->createTutor();
                    break;
                case 'PUT':
                    $id
                        ? $this->updateTutor($id)
                        : $this->respond(false, null, 'ID mancante per PUT.', 400);
                    break;
                case 'DELETE':
                    $id
                        ? $this->deleteTutor($id)
                        : $this->respond(false, null, 'ID mancante per DELETE.', 400);
                    break;
                default:
                    $this->respond(false, null, 'Metodo HTTP non supportato.', 405);
            }
        } catch (PDOException $e) {
            error_log('[FSL][TutorScolasticiApi] PDOException: ' . $e->getMessage());
            $this->respond(false, null, 'Errore del database. Riprova più tardi.', 500);
        } catch (JsonException $e) {
            $this->respond(false, null, 'Errore di encoding JSON.', 500);
        }
    }

    // ── READ ALL ─────────────────────────────────────────────

    private function listTutor(): never
    {
        $rows = $this->pdo
            ->query('SELECT codice_docente, nome, cognome, tipo, numero_studenti
                     FROM TUTOR_SCOLASTICO ORDER BY codice_docente DESC')
            ->fetchAll();
        $this->respond(true, $rows);
    }

    // ── READ ONE ─────────────────────────────────────────────

    private function getTutor(int $id): never
    {
        $stmt = $this->pdo->prepare(
            'SELECT codice_docente, nome, cognome, tipo, numero_studenti
             FROM TUTOR_SCOLASTICO WHERE codice_docente = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            $this->respond(false, null, "Tutor scolastico con ID {$id} non trovato.", 404);
        }
        $this->respond(true, $row);
    }

    // ── CREATE ───────────────────────────────────────────────

    private function createTutor(): never
    {
        $fields = $this->validateFields($this->parseBody());
        $stmt = $this->pdo->prepare(
            'INSERT INTO TUTOR_SCOLASTICO (nome, cognome, tipo, numero_studenti)
             VALUES (:nome, :cognome, :tipo, :numero_studenti)'
        );
        $stmt->execute([
            ':nome'            => $fields['nome'],
            ':cognome'         => $fields['cognome'],
            ':tipo'            => $fields['tipo'],
            ':numero_studenti' => $fields['numero_studenti'],
        ]);
        $this->respond(true, ['codice_docente' => (int) $this->pdo->lastInsertId()], 'Tutor scolastico creato con successo.', 201);
    }

    // ── UPDATE ───────────────────────────────────────────────

    private function updateTutor(int $id): never
    {
        $this->ensureExists($id);
        $fields = $this->validateFields($this->parseBody());
        $stmt = $this->pdo->prepare(
            'UPDATE TUTOR_SCOLASTICO
             SET nome = :nome, cognome = :cognome, tipo = :tipo, numero_studenti = :numero_studenti
             WHERE codice_docente = :id'
        );
        $stmt->execute([
            ':nome'            => $fields['nome'],
            ':cognome'         => $fields['cognome'],
            ':tipo'            => $fields['tipo'],
            ':numero_studenti' => $fields['numero_studenti'],
            ':id'              => $id,
        ]);
        $this->respond(true, ['codice_docente' => $id], 'Tutor scolastico aggiornato con successo.');
    }

    // ── DELETE ───────────────────────────────────────────────

    private function deleteTutor(int $id): never
    {
        $this->ensureExists($id);
        $stmt = $this->pdo->prepare('DELETE FROM TUTOR_SCOLASTICO WHERE codice_docente = :id');
        $stmt->execute([':id' => $id]);
        $this->respond(true, null, 'Tutor scolastico eliminato con successo.');
    }

    // ── HELPERS PRIVATI ──────────────────────────────────────

    private function ensureExists(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT codice_docente FROM TUTOR_SCOLASTICO WHERE codice_docente = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            $this->respond(false, null, "Tutor scolastico con ID {$id} non trovato.", 404);
        }
    }

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            $this->respond(false, null, 'Body della richiesta vuoto.', 400);
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            $this->respond(false, null, 'Body non è un oggetto JSON valido.', 400);
        }
        return $data;
    }

    private function validateFields(array $body): array
    {
        $errors       = [];
        $allowedTipo  = ['dipartimento', 'area disciplinare'];
        $nome         = trim((string) ($body['nome']    ?? ''));
        $cognome      = trim((string) ($body['cognome'] ?? ''));
        $tipo         = trim((string) ($body['tipo']    ?? ''));
        $numStudenti  = isset($body['numero_studenti']) ? (int) $body['numero_studenti'] : null;

        if ($nome    === '')                              $errors[] = 'nome è obbligatorio.';
        if ($cognome === '')                              $errors[] = 'cognome è obbligatorio.';
        if (!in_array($tipo, $allowedTipo, true))        $errors[] = 'tipo non valido.';
        if ($numStudenti === null || $numStudenti < 0)   $errors[] = 'numero_studenti non valido.';

        if ($errors) {
            $this->respond(false, ['errors' => $errors], implode(' | ', $errors), 422);
        }
        return [
            'nome'            => $nome,
            'cognome'         => $cognome,
            'tipo'            => $tipo,
            'numero_studenti' => $numStudenti,
        ];
    }

    private function respond(bool $success, mixed $data, string $message = '', int $code = 200): never
    {
        http_response_code($code);
        echo json_encode(
            ['success' => $success, 'message' => $message, 'data' => $data],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}


// ════════════════════════════════════════════════════════════
// ENTRY POINT — Router principale
// Eseguito SOLO se api.php viene chiamato direttamente.
// Quando un middleware fa require_once 'api.php', questo blocco
// viene saltato: il middleware istanzia la classe da solo.
// ════════════════════════════════════════════════════════════

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {

    $entity = $_GET['entity'] ?? '';

    $entityMap = [
        'aziende'          => AziendeApi::class,
        'disponibilita'    => DisponibilitaApi::class,
        'esperienze'       => EsperienzeApi::class,
        'tutor_aziendali'  => TutorAziendaliApi::class,
        'tutor_scolastici' => TutorScolasticiApi::class,
    ];

    if (!isset($entityMap[$entity])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Parametro "entity" mancante o non valido. '
                       . 'Valori accettati: ' . implode(', ', array_keys($entityMap)),
            'data'    => null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $api = new $entityMap[$entity]();
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

}
