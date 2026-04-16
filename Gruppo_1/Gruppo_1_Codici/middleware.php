<?php
declare(strict_types=1);

header("Content-Type: application/json");

require_once "sessioni.php";
require_once "api.php";

// sicurezza sessione
if (!isset($_SESSION['utente'])) {
    http_response_code(401);
    echo json_encode(["errore" => "Non autenticato"]);
    exit;
}

// input JSON
$input = json_decode(file_get_contents("php://input"), true) ?? [];

// routing REST
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            echo json_encode(getAziende());
            break;

        case 'POST':
            echo json_encode(createAzienda($input));
            break;

        case 'PUT':
            echo json_encode(updateAzienda($input));
            break;

        case 'DELETE':
            echo json_encode(deleteAzienda($input));
            break;

        default:
            http_response_code(405);
            echo json_encode(["errore" => "Metodo non consentito"]);
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(["errore" => $e->getMessage()]);
}