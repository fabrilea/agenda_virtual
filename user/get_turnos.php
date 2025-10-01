<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "USER") {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$turnos = $database->getReference('turnos')->getValue() ?: [];
$eventos = [];
$idUsuario = $_SESSION['user']['id'];

foreach ($turnos as $id => $t) {
    if ($t['estado'] === 'DISPONIBLE') {
        $eventos[] = [
            'id' => $id,
            'title' => "DISPONIBLE",
            'start' => $t['fecha']."T".$t['hora'],
            'color' => 'green'
        ];
    } elseif ($t['estado'] === 'RESERVADO' && $t['usuarioId'] === $idUsuario) {
        $eventos[] = [
            'id' => $id,
            'title' => "MI TURNO",
            'start' => $t['fecha']."T".$t['hora'],
            'color' => 'blue'
        ];
    }
}

echo json_encode(['success' => true, 'eventos' => $eventos]);
