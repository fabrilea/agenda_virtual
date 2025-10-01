<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$idTurno = $data['id'] ?? null;
$idUsuario = $_SESSION['user']['id'] ?? null;

if (!$idTurno || !$idUsuario) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error: turno inválido o usuario no autenticado.']);
    exit;
}

$turno = $database->getReference('turnos/'.$idTurno)->getValue();

if ($turno && $turno['estado'] === 'DISPONIBLE') {
    $database->getReference('turnos/'.$idTurno)->update([
        'estado' => 'RESERVADO',
        'usuarioId' => $idUsuario
    ]);
    echo json_encode(['success' => true, 'message' => 'Turno reservado con éxito.']);
} else {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Este turno ya no está disponible.']);
}
