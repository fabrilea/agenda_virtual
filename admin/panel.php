<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Turnos</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h2>Panel del Administrador</h2>
    <div id='calendar'></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            events: 'get_turnos_admin.php',

            dateClick: function(info) {
                let fecha = info.dateStr;
                let hora = prompt("Ingrese hora para habilitar turno (HH:MM):");
                if (hora) {
                    fetch("crear_turno.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ fecha: fecha, hora: hora })
                    })
                    .then(r => r.text())
                    .then(msg => {
                        alert(msg);
                        calendar.refetchEvents();
                    });
                }
            },

            eventClick: function(info) {
                if (confirm("¿Desea cancelar este turno?")) {
                    fetch("cancelar_turno.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ id: info.event.id })
                    })
                    .then(r => r.text())
                    .then(msg => {
                        alert(msg);
                        calendar.refetchEvents();
                    });
                }
            }
        });
        calendar.render();
    });
    </script>
</body>
</html>
