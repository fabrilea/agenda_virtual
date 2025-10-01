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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    #calendar {
      font-size: 1.1rem;
      min-height: 500px;
    }
    .table td, .table th {
      font-size: 1.1rem;
      padding: 1rem;
    }
    .btn {
      font-size: 1.1rem;
      padding: 0.75rem 1.25rem;
    }
  </style>
</head>
<body class="bg-light">

<?php include '../sidebar.php'; ?>

<div class="container-fluid py-3">
  <div class="card shadow p-4 mb-4">
    <h2 class="mb-4 text-center">📅 Agenda de <?= htmlspecialchars($_SESSION['user']['nombre']) ?></h2>
    <div id="calendar"></div>
  </div>

  <div class="card shadow p-4">
    <h3 class="mb-3 text-center">🗂 Mis Turnos</h3>
    <div class="table-responsive">
      <table class="table table-striped align-middle text-center" id="tabla-turnos">
        <thead class="table-dark">
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="3">Cargando...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function cargarMisTurnos() {
  fetch("mis_turnos.php")
    .then(r => r.json())
    .then(data => {
      let tbody = document.querySelector("#tabla-turnos tbody");
      tbody.innerHTML = "";

      if (!data.success || data.turnos.length === 0) {
        tbody.innerHTML = "<tr><td colspan='3'>No tienes turnos reservados</td></tr>";
        return;
      }

      data.turnos.forEach(t => {
        let tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${t.fecha}</td>
          <td>${t.hora}</td>
          <td>
            <button class="btn btn-sm btn-danger" onclick="cancelarTurno('${t.id}')">Cancelar</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    })
    .catch(err => {
      console.error("Error al cargar turnos:", err);
      document.querySelector("#tabla-turnos tbody").innerHTML =
        "<tr><td colspan='3'>Error cargando turnos</td></tr>";
    });
}

function cancelarTurno(id) {
  if (confirm("¿Cancelar este turno?")) {
    fetch("cancelar_turno_user.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        calendar.refetchEvents();
        cargarMisTurnos();
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var initialView = window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth';

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: initialView,
    locale: 'es',
    height: "auto",
    events: 'get_turnos.php',
    headerToolbar: {
      left: 'prev,next',   // flechas izquierda
      center: 'title',     // título centrado
      right: 'today'       // botón Hoy a la derecha
  },
  buttonText: { today: 'Hoy' },
    eventClick: function(info) {
      if (info.event.title === "DISPONIBLE") {
        if (confirm("¿Reservar este turno?")) {
          fetch("reservar_turno.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: info.event.id })
          })
          .then(r => r.json())
          .then(data => {
            alert(data.message);
            if (data.success) {
              calendar.refetchEvents();
              cargarMisTurnos();
            }
          });
        }
      } else if (info.event.title === "MI TURNO") {
        alert("Ya reservaste este turno. Si querés cancelarlo usá la tabla de abajo.");
      }
    }
  });

  calendar.render();
  cargarMisTurnos();
});
</script>

</body>
</html>
