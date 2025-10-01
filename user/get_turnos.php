<?php
session_start();
require '../config.php';

$turnos = $database->getReference('turnos')->getValue();
$eventos = [];
$idUsuario = $_SESSION['user']['id'];

if ($turnos) {
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
}

header('Content-Type: application/json');
echo json_encode($eventos);
