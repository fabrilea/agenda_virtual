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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
</head>
<body class="bg-light">

<?php include '../sidebar.php'; ?>

<div class="container py-4">
  <div class="card shadow p-4 mb-4">
    <h2 class="mb-4">Agenda de <?= htmlspecialchars($_SESSION['user']['nombre']) ?></h2>
    <div id="calendar"></div>
  </div>

  <!-- 🔹 Tabla de mis turnos -->
  <div class="card shadow p-4">
    <h3 class="mb-3">Mis Turnos</h3>
    <table class="table table-striped" id="tabla-turnos">
      <thead>
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

<script>
function cargarMisTurnos() {
  fetch("mis_turnos.php")
    .then(r => r.json())
    .then(turnos => {
      let tbody = document.querySelector("#tabla-turnos tbody");
      tbody.innerHTML = "";

      if (!turnos || turnos.length === 0) {
        tbody.innerHTML = "<tr><td colspan='3'>No tienes turnos reservados</td></tr>";
        return;
      }

      turnos.forEach(t => {
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
        "<tr><td colspan='3'>Error al cargar turnos</td></tr>";
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
  window.calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    events: 'get_turnos.php',
    locale: 'es',
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
      }
    }
  });
  calendar.render();

  // 🔹 cargar tabla apenas arranca
  cargarMisTurnos();
});
</script>

</body>
</html>
