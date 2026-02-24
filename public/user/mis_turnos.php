<?php
session_start();
require '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "USER") {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['user']['id'];

try {
    $turnos = $database->getReference('turnos')->getValue() ?: [];
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener los turnos.']);
    exit;
}

$misTurnos = [];
foreach ($turnos as $id => $t) {
    $estado = $t['estado'] ?? '';
    $esDelUsuario = ($t['usuarioId'] ?? '') === $idUsuario;

    if ($esDelUsuario && in_array($estado, ['PENDIENTE', 'CONFIRMADO', 'RESERVADO'], true)) {
        $misTurnos[] = [
            'id'     => $id,
            'fecha'  => $t['fecha'],
            'hora'   => $t['hora'],
            'estado' => $estado,
        ];
    }
}

echo json_encode(['success' => true, 'turnos' => $misTurnos]);
