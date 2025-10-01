<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "USER") {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Agenda</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
</head>
<body>
    <h2>Agenda de <?= htmlspecialchars($_SESSION['user']['nombre']) ?></h2>
    <div id='calendar'></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: 'get_turnos.php',

            eventClick: function(info) {
                if (info.event.title === "DISPONIBLE") {
                    if (confirm("¿Reservar este turno?")) {
                        fetch("reservar_turno.php", {
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
                } else if (info.event.title === "MI TURNO") {
                    if (confirm("¿Cancelar este turno?")) {
                        fetch("cancelar_turno_user.php", {
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
                } else {
                    alert("Este turno no está disponible.");
                }
            }
        });
        calendar.render();
    });
    </script>
</body>
</html>
