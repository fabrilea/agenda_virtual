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

if ($turno && $turno['estado'] === 'RESERVADO' && $turno['usuarioId'] === $idUsuario) {
    $fechaHoraTurno = strtotime($turno['fecha'] . " " . $turno['hora']);
    $ahora = time();
    $diferenciaHoras = ($fechaHoraTurno - $ahora) / 3600;

    if ($diferenciaHoras >= 48) {
        $database->getReference('turnos/'.$idTurno)->update([
            'estado' => 'DISPONIBLE',
            'usuarioId' => null
        ]);
        echo json_encode(['success' => true, 'message' => 'Turno cancelado. El horario volvió a estar disponible.']);
    } else {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo puedes cancelar con al menos 48 horas de anticipación.']);
    }
} else {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No puedes cancelar este turno.']);
}
