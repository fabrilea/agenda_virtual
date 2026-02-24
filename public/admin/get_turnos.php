<?php
session_start();
require '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$rol       = $_SESSION['user']['rol'];
$idUsuario = $_SESSION['user']['id'];

try {
    $turnos   = $database->getReference('turnos')->getValue() ?: [];
    $usuarios = $database->getReference('usuarios')->getValue() ?: [];
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([]);
    exit;
}

$eventos = [];

foreach ($turnos as $id => $t) {
    // Si es ADMIN, mostrar todos los turnos
    if ($rol === "ADMIN") {
        if ($t['estado'] === 'DISPONIBLE') {
            $eventos[] = [
                'id'              => $id,
                'title'           => "DISPONIBLE",
                'start'           => $t['fecha']."T".$t['hora'],
                'color'           => 'green',
                'extendedProps'   => ['estado' => 'DISPONIBLE']
            ];
        } elseif ($t['estado'] === 'PENDIENTE') {
            $usuarioNombre = $usuarios[$t['usuarioId']]['nombre'] ?? 'Desconocido';
            $eventos[] = [
                'id'    => $id,
                'title' => "\u23f3 PENDIENTE - " . $usuarioNombre,
                'start' => $t['fecha']."T".$t['hora'],
                'color' => '#f59e0b',
                'extendedProps' => [
                    'estado'    => 'PENDIENTE',
                    'usuarioId' => $t['usuarioId'] ?? null,
                ]
            ];
        } elseif ($t['estado'] === 'CONFIRMADO') {
            $usuarioNombre = $usuarios[$t['usuarioId']]['nombre'] ?? 'Desconocido';
            $eventos[] = [
                'id'    => $id,
                'title' => "\u2705 CONFIRMADO - " . $usuarioNombre,
                'start' => $t['fecha']."T".$t['hora'],
                'color' => '#16a34a',
                'extendedProps' => [
                    'estado'    => 'CONFIRMADO',
                    'usuarioId' => $t['usuarioId'] ?? null,
                ]
            ];
        } elseif ($t['estado'] === 'RESERVADO') {
            // Estado legacy: mostrar igual que PENDIENTE
            $usuarioNombre = $usuarios[$t['usuarioId']]['nombre'] ?? "Desconocido";
            $eventos[] = [
                'id' => $id,
                'title' => "RESERVADO - " . $usuarioNombre,
                'start' => $t['fecha']."T".$t['hora'],
                'color' => 'red',
                'extendedProps' => [
                    'estado'    => 'RESERVADO',
                    'usuarioId' => $t['usuarioId'] ?? null,
                ]
            ];
        } elseif ($t['estado'] === 'CANCELADO') {
            $eventos[] = [
                'id'    => $id,
                'title' => "CANCELADO",
                'start' => $t['fecha']."T".$t['hora'],
                'color' => 'gray',
                'extendedProps' => ['estado' => 'CANCELADO']
            ];
        }
    }
}

echo json_encode($eventos);
