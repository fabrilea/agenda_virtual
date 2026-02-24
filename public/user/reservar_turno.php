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

// ⚠️ NOTA: Para eliminar la race condition (TOCTOU), esta operación debe usar
// Firebase RTDB Transactions: $database->getReference(...)->runTransaction(...).
// La siguiente implementación es una mejora sobre el código original pero no es atómica.
try {
    $turno = $database->getReference('turnos/'.$idTurno)->getValue();

    if ($turno && $turno['estado'] === 'DISPONIBLE') {
        $database->getReference('turnos/'.$idTurno)->update([
            'estado'    => 'PENDIENTE',
            'usuarioId' => $idUsuario
        ]);

        // 🔔 Notificar al usuario: turno pendiente de confirmación
        notificarCambioTurno(
            $_SESSION['user']['email']    ?? '',
            $_SESSION['user']['nombre']    ?? '',
            'PENDIENTE',
            $turno['fecha'],
            $turno['hora'],
            $_SESSION['user']['telefono'] ?? ''
        );

        echo json_encode(['success' => true, 'message' => 'Turno reservado. Quedó PENDIENTE de confirmación por el administrador.']);
    } else {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Este turno ya no está disponible.']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud.']);
}
