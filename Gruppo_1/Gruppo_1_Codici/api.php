<?php
declare(strict_types=1);
require_once "db_conn.php";

/**
 * SANITIZZAZIONE
 */
function clean(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

/**
 * VALIDAZIONE
 */
function valida(array $d): array {
    if (empty($d['ragione_sociale']) || strlen($d['ragione_sociale']) < 2)
        throw new Exception("Ragione sociale non valida");

    if (!preg_match('/^[0-9]{11}$/', $d['partita_iva']))
        throw new Exception("Partita IVA non valida");

    return [
        'ragione_sociale' => clean($d['ragione_sociale']),
        'partita_iva' => $d['partita_iva'],
        'sede_legale' => clean($d['sede_legale'] ?? ''),
        'sede_operativa' => clean($d['sede_operativa'] ?? '')
    ];
}

/**
 * READ
 */
function getAziende(): array {
    global $pdo;

    $stmt = $pdo->query("SELECT * FROM AZIENDA ORDER BY codice_azienda DESC");
    return $stmt->fetchAll();
}

/**
 * CREATE
 */
function createAzienda(array $data): array {
    global $pdo;

    $d = valida($data);

    $sql = "INSERT INTO AZIENDA 
            (ragione_sociale, partita_iva, sede_legale, sede_operativa)
            VALUES (:r, :p, :sl, :so)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':r' => $d['ragione_sociale'],
        ':p' => $d['partita_iva'],
        ':sl' => $d['sede_legale'],
        ':so' => $d['sede_operativa']
    ]);

    return ["success" => true, "id" => $pdo->lastInsertId()];
}

/**
 * UPDATE
 */
function updateAzienda(array $data): array {
    global $pdo;

    if (empty($data['codice_azienda']))
        throw new Exception("ID mancante");

    $d = valida($data);

    $sql = "UPDATE AZIENDA SET
            ragione_sociale=:r,
            partita_iva=:p,
            sede_legale=:sl,
            sede_operativa=:so
            WHERE codice_azienda=:id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':r' => $d['ragione_sociale'],
        ':p' => $d['partita_iva'],
        ':sl' => $d['sede_legale'],
        ':so' => $d['sede_operativa'],
        ':id' => $data['codice_azienda']
    ]);

    return ["success" => true];
}

/**
 * DELETE
 */
function deleteAzienda(array $data): array {
    global $pdo;

    if (empty($data['codice_azienda']))
        throw new Exception("ID mancante");

    $stmt = $pdo->prepare("DELETE FROM AZIENDA WHERE codice_azienda=:id");
    $stmt->execute([':id' => $data['codice_azienda']]);

    return ["success" => true];
}