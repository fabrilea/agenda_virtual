<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$idTurno = $data['id'] ?? null;

if (!$idTurno) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Turno inválido']);
    exit;
}

$turno = $database->getReference('turnos/'.$idTurno)->getValue();

if ($turno) {
    $database->getReference('turnos/'.$idTurno)->update([
        'estado' => 'CANCELADO'
    ]);
    echo json_encode(['success' => true, 'message' => 'Turno cancelado por el administrador.']);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Turno no encontrado.']);
}
