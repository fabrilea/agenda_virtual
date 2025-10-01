<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$turnos = $database->getReference('turnos')->getValue() ?: [];
$eventos = [];

foreach ($turnos as $id => $t) {
    $color = 'gray';
    $title = 'Turno';

    if ($t['estado'] === 'DISPONIBLE') {
        $title = 'DISPONIBLE';
        $color = 'green';
    } elseif ($t['estado'] === 'RESERVADO') {
        $title = 'RESERVADO';
        $color = 'blue';
    } elseif ($t['estado'] === 'CANCELADO') {
        $title = 'CANCELADO';
        $color = 'red';
    }

    $eventos[] = [
        'id' => $id,
        'title' => $title,
        'start' => $t['fecha']."T".$t['hora'],
        'color' => $color
    ];
}

echo json_encode(['success' => true, 'eventos' => $eventos]);
