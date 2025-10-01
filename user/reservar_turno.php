<?php
session_start();
require '../config.php';

$data = json_decode(file_get_contents("php://input"), true);
$idTurno = $data['id'] ?? null;
$idUsuario = $_SESSION['user']['id'];

if (!$idTurno) {
    http_response_code(400);
    echo "Error: turno inválido.";
    exit;
}

$turno = $database->getReference('turnos/'.$idTurno)->getValue();

if ($turno && $turno['estado'] === 'DISPONIBLE') {
    $database->getReference('turnos/'.$idTurno)->update([
        'estado' => 'RESERVADO',
        'usuarioId' => $idUsuario
    ]);
    echo "Turno reservado con éxito.";
} else {
    echo "Este turno ya no está disponible.";
}
