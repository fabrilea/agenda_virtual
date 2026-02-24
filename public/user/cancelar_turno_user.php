<?php
session_start();
require '../../config.php';
require '../notificaciones.php';

header('Content-Type: application/json');

// 🔐 Guard de autenticación explícito
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'USER') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado.']);
    exit;
}

$data      = json_decode(file_get_contents("php://input"), true);
$idTurno   = $data['id'] ?? null;
$idUsuario = $_SESSION['user']['id'];

// 🛡️ Prevenir path traversal: solo caracteres alfanuméricos y guiones
if (!$idTurno || !preg_match('/^[\w-]+$/', $idTurno)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de turno inválido.']);
    exit;
}

try {
    $turno = $database->getReference('turnos/'.$idTurno)->getValue();

    if ($turno && $turno['estado'] === 'RESERVADO' && $turno['usuarioId'] === $idUsuario) {
        $fechaHoraTurno = strtotime($turno['fecha'] . " " . $turno['hora']);
        $ahora = time();
        $diferenciaHoras = ($fechaHoraTurno - $ahora) / 3600;

        if ($diferenciaHoras >= 48) {
            $database->getReference('turnos/'.$idTurno)->update([
                'estado'    => 'DISPONIBLE',
                'usuarioId' => null
            ]);

            // 🔔 Notificar al usuario la cancelación
            notificarCambioTurno(
                $_SESSION['user']['email']    ?? '',
                $_SESSION['user']['nombre']   ?? '',
                'CANCELADO',
                $turno['fecha'],
                $turno['hora'],
                $_SESSION['user']['telefono'] ?? ''
            );

            echo json_encode(['success' => true, 'message' => 'Turno cancelado. El horario volvió a estar disponible.']);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Solo puedes cancelar con al menos 48 horas de anticipación.']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No puedes cancelar este turno.']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud.']);
}
