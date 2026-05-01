<?php
/**
 * api.php — FSL Panel · API Layer azienda
 *
 * Classe AziendeApi: incapsula tutte le operazioni CRUD sulla
 * tabella azienda usando PDO con prepared statements.
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
             FROM azienda
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
             FROM azienda
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

    // ════════════════════════════════════════════════════════
    // DELETE
    // ════════════════════════════════════════════════════════

    /**
     * Elimina un'azienda per ID.
     *
     * ATTENZIONE: la tabella DISPONIBILITA ha FK con ON DELETE CASCADE
     * verso azienda, quindi le disponibilità collegate verranno
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

    // ════════════════════════════════════════════════════════
    // UTILITY PRIVATA
    // ════════════════════════════════════════════════════════

    /**
     * Verifica se una partita IVA è già usata (opzionale, per messaggi user-friendly).
     */
    private function pivaExists(string $piva, ?int $excludeId = null): bool
    {
        $sql  = 'SELECT 1 FROM azienda WHERE partita_iva = :piva';
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
