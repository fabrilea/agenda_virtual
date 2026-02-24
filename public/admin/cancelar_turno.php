<?php
session_start();
require '../../config.php';
require '../notificaciones.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$data    = json_decode(file_get_contents("php://input"), true);
$idTurno = $data['id'] ?? null;

// 🛡️ Prevenir path traversal: solo caracteres alfanuméricos y guiones
if (!$idTurno || !preg_match('/^[\w-]+$/', $idTurno)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de turno inválido']);
    exit;
}

try {
    $turno = $database->getReference('turnos/'.$idTurno)->getValue();

    if ($turno) {
        $idUsuarioCancelado = $turno['usuarioId'] ?? null;

        $updateData = [
            'estado'           => 'CANCELADO',
            'usuarioId'        => null,
            'canceladoPor'     => $_SESSION['user']['id'],
            'fechaCancelacion' => date('Y-m-d H:i:s')
        ];

        $database->getReference('turnos/'.$idTurno)->update($updateData);

        // 🔔 Notificar al usuario si tenía este turno asignado
        if ($idUsuarioCancelado) {
            $usuario = $database->getReference('usuarios/' . $idUsuarioCancelado)->getValue();
            if ($usuario) {
                notificarCambioTurno(
                    $usuario['email']    ?? '',
                    $usuario['nombre']   ?? 'Usuario',
                    'CANCELADO',
                    $turno['fecha'],
                    $turno['hora'],
                    $usuario['telefono'] ?? ''
                );
            }
        }

        echo json_encode(['success' => true, 'message' => 'Turno cancelado por el administrador.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Turno no encontrado.']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud.']);
}
