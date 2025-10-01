<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$idTurno = $data['id'] ?? null;

if (!$idTurno) {
    http_response_code(400);
    echo "Falta ID de turno.";
    exit;
}

try {
    // Marcar como CANCELADO (no borramos para mantener historial)
    $database->getReference('turnos/'.$idTurno)->update([
        'estado' => 'CANCELADO'
    ]);
    echo "Turno cancelado correctamente.";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error al cancelar: " . $e->getMessage();
}
