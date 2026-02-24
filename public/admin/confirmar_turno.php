<?php
session_start();
require '../../config.php';
require '../notificaciones.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$data    = json_decode(file_get_contents("php://input"), true);
$idTurno = $data['id'] ?? null;

// 🛡️ Prevenir path traversal
if (!$idTurno || !preg_match('/^[\w-]+$/', $idTurno)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de turno inválido']);
    exit;
}

try {
    $turno = $database->getReference('turnos/' . $idTurno)->getValue();

    if (!$turno) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Turno no encontrado.']);
        exit;
    }

    if ($turno['estado'] !== 'PENDIENTE') {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Solo se pueden confirmar turnos en estado PENDIENTE.']);
        exit;
    }

    $idUsuario = $turno['usuarioId'];

    $database->getReference('turnos/' . $idTurno)->update([
        'estado'          => 'CONFIRMADO',
        'confirmadoPor'   => $_SESSION['user']['id'],
        'fechaConfirmacion' => date('Y-m-d H:i:s'),
    ]);

    // 🔔 Obtener datos del usuario para notificar
    $usuario = $database->getReference('usuarios/' . $idUsuario)->getValue();
    if ($usuario) {
        notificarCambioTurno(
            $usuario['email']    ?? '',
            $usuario['nombre']   ?? 'Usuario',
            'CONFIRMADO',
            $turno['fecha'],
            $turno['hora'],
            $usuario['telefono'] ?? ''
        );
    }

    echo json_encode(['success' => true, 'message' => 'Turno confirmado y usuario notificado.']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud.']);
}
