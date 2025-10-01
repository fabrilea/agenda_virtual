<?php
session_start();
require '../config.php';

$turnos = $database->getReference('turnos')->getValue();
$adminId = $_SESSION['user']['id'];
$eventos = [];

if ($turnos) {
    foreach ($turnos as $id => $t) {
        if ($t['adminId'] === $adminId) {
            $color = 'green';
            if ($t['estado'] === 'DISPONIBLE') $color = 'green';
            if ($t['estado'] === 'RESERVADO') $color = 'blue';
            if ($t['estado'] === 'CANCELADO') $color = 'red';

            $eventos[] = [
                'id' => $id,
                'title' => $t['estado'],
                'start' => $t['fecha']."T".$t['hora'],
                'color' => $color
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($eventos);
