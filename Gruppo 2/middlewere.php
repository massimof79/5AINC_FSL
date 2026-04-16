<?php
require_once 'Class.Disponibilita.php';

$config = [
    'host' => 'localhost',
    'db'   => '5AinC_FSL',
    'user' => 'root',
    'pass' => ''
];

$manager = new DisponibilitaManager($config);
$method = $_SERVER['REQUEST_METHOD'];

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) && $method !== 'GET') {
    http_response_code(403);
    echo json_encode(["error" => "Non autorizzato"]);
    exit;
}

switch($method) {
    case 'GET':
        echo json_encode($manager->getAll());
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (isset($input['data'], $input['ora_inizio'], $input['ora_fine'])) {
            $success = $manager->create($input['data'], $input['ora_inizio'], $input['ora_fine']);
            echo json_encode(["success" => $success]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Dati incompleti"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $success = $manager->delete($_GET['id']);
            echo json_encode(["deleted" => $success]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ID mancante"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Metodo non supportato"]);
        break;
}
?>