<?php
session_start();
require '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$data    = json_decode(file_get_contents("php://input"), true);
$fecha   = $data['fecha'] ?? null;
$hora    = $data['hora']  ?? null;
$adminId = $_SESSION['user']['id'];

// 🛡️ Validar formato de fecha (YYYY-MM-DD) y hora (HH:MM)
if (!$fecha || !$hora) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
$horaObj  = DateTime::createFromFormat('H:i', $hora);

if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha ||
    !$horaObj  || $horaObj->format('H:i') !== $hora) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato de fecha u hora inválido']);
    exit;
}

// Impedir fechas en el pasado
$fechaHora = new DateTime($fecha . ' ' . $hora);
if ($fechaHora <= new DateTime()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se pueden crear turnos en fechas pasadas']);
    exit;
}

try {
    $database->getReference('turnos')->push([
        'fecha'     => $fecha,
        'hora'      => $hora,
        'estado'    => 'DISPONIBLE',
        'adminId'   => $adminId,
        'usuarioId' => null
    ]);
    echo json_encode(['success' => true, 'message' => 'Turno creado']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al crear el turno.']);
}
