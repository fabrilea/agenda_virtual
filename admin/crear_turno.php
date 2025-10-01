<?php
session_start();
require '../config.php';

header('Content-Type: application/json'); // 👈 siempre devolvemos JSON

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$fecha = $data['fecha'] ?? null;
$hora = $data['hora'] ?? null;
$adminId = $_SESSION['user']['id'];

if ($fecha && $hora) {
    $database->getReference('turnos')->push([
        'fecha' => $fecha,
        'hora' => $hora,
        'estado' => 'DISPONIBLE',
        'adminId' => $adminId,
        'usuarioId' => null
    ]);

    echo json_encode(['success' => true, 'message' => 'Turno creado']);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}
