<?php
    require_once 'db_conn.php';
    header("Content-Type: application/json");
    $method = $_SERVER['REQUEST_METHOD'];
    switch($method) {
    case 'GET': // READ
    $stmt = $pdo->query("SELECT * FROM Azienda");
    echo json_encode($stmt->fetchAll());
    break;
    case 'POST': // CREATE
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO Azienda (nome, p_iva, indirizzo) VALUES (?, ?, ?)";
    $pdo->prepare($sql)->execute([$data['nome'], $data['p_iva'], $data['indirizzo']]);
    echo json_encode(["status" => "Success"]);
    break;
    case 'PUT': // UPDATE
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "UPDATE Azienda SET nome=?, p_iva=?, indirizzo=? WHERE id=?";
    $pdo->prepare($sql)->execute([$data['nome'], $data['p_iva'], $data['indirizzo'],

    $data['id']]);
    echo json_encode(["status" => "Updated"]);
    break;
    case 'DELETE': // DELETE
    $id = $_GET['id'];
    $sql = "DELETE FROM Azienda WHERE id = ?";
    $pdo->prepare($sql)->execute([$id]);
    echo json_encode(["status" => "Deleted"]);
    break;
    }
?>