<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "USER") {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['user']['id'];
$turnos = $database->getReference('turnos')->getValue() ?: [];

$misTurnos = [];
foreach ($turnos as $id => $t) {
    if ($t['estado'] === 'RESERVADO' && $t['usuarioId'] === $idUsuario) {
        $misTurnos[] = [
            'id' => $id,
            'fecha' => $t['fecha'],
            'hora' => $t['hora']
        ];
    }
}

echo json_encode(['success' => true, 'turnos' => $misTurnos]);
