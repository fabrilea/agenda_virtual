<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    http_response_code(403);
    echo "Acceso denegado";
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
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Turno creado']);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}
