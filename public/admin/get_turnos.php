<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$rol = $_SESSION['user']['rol'];
$idUsuario = $_SESSION['user']['id'];

$turnos = $database->getReference('turnos')->getValue() ?: [];
$usuarios = $database->getReference('usuarios')->getValue() ?: [];

$eventos = [];

foreach ($turnos as $id => $t) {
    // Si es ADMIN, mostrar todos los turnos
    if ($rol === "ADMIN") {
        if ($t['estado'] === 'DISPONIBLE') {
            $eventos[] = [
                'id' => $id,
                'title' => "DISPONIBLE",
                'start' => $t['fecha']."T".$t['hora'],
                'color' => 'green'
            ];
        } elseif ($t['estado'] === 'RESERVADO') {
            $usuarioNombre = $usuarios[$t['usuarioId']]['nombre'] ?? "Desconocido";
            $eventos[] = [
                'id' => $id,
                'title' => "RESERVADO - " . $usuarioNombre,
                'start' => $t['fecha']."T".$t['hora'],
                'color' => 'red'
            ];
        } elseif ($t['estado'] === 'CANCELADO') {
            $eventos[] = [
                'id' => $id,
                'title' => "CANCELADO",
                'start' => $t['fecha']."T".$t['hora'],
                'color' => 'gray'
            ];
        }
    }
}

echo json_encode($eventos);
