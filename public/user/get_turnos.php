<?php
session_start();
require '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "USER") {
    http_response_code(401);
    echo json_encode([]); // array vacío en caso de no autorizado
    exit;
}

$idUsuario = $_SESSION['user']['id'];

try {
    $turnos = $database->getReference('turnos')->getValue() ?: [];
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([]);
    exit;
}

$eventos = [];

foreach ($turnos as $id => $t) {
    $estado    = $t['estado']    ?? '';
    $usuarioId = $t['usuarioId'] ?? '';

    if ($estado === 'DISPONIBLE') {
        $eventos[] = [
            'id'    => $id,
            'title' => 'DISPONIBLE',
            'start' => $t['fecha']."T".$t['hora'],
            'color' => 'green'
        ];
    } elseif ($estado === 'PENDIENTE' && $usuarioId === $idUsuario) {
        $eventos[] = [
            'id'    => $id,
            'title' => '\u23f3 MI TURNO (pendiente)',
            'start' => $t['fecha']."T".$t['hora'],
            'color' => '#f59e0b'
        ];
    } elseif ($estado === 'CONFIRMADO' && $usuarioId === $idUsuario) {
        $eventos[] = [
            'id'    => $id,
            'title' => '\u2705 MI TURNO (confirmado)',
            'start' => $t['fecha']."T".$t['hora'],
            'color' => '#16a34a'
        ];
    } elseif ($estado === 'RESERVADO' && $usuarioId === $idUsuario) {
        // legacy
        $eventos[] = [
            'id'    => $id,
            'title' => 'MI TURNO',
            'start' => $t['fecha']."T".$t['hora'],
            'color' => 'blue'
        ];
    }
}

echo json_encode($eventos);
