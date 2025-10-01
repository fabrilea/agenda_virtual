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

if ($turno && $turno['estado'] === 'RESERVADO' && $turno['usuarioId'] === $idUsuario) {
    // calcular diferencia en horas
    $fechaHoraTurno = strtotime($turno['fecha'] . " " . $turno['hora']);
    $ahora = time();
    $diferenciaHoras = ($fechaHoraTurno - $ahora) / 3600;

    if ($diferenciaHoras >= 48) {
        // 🔥 volvemos el turno a DISPONIBLE para que otro paciente lo pueda reservar
        $database->getReference('turnos/'.$idTurno)->update([
            'estado' => 'DISPONIBLE',
            'usuarioId' => null
        ]);
        echo "Turno cancelado. El horario volvió a estar disponible.";
    } else {
        echo "Solo puedes cancelar con al menos 48 horas de anticipación.";
    }
} else {
    echo "No puedes cancelar este turno.";
}
