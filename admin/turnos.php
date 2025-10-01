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
  <title>Gestión de Turnos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<?php include '../sidebar.php'; ?>

<div class="container-fluid py-3">
  <h2 class="mb-4 text-center">📅 Gestión de Turnos</h2>

  <div class="card shadow p-3">
    <div id="calendar"></div>
  </div>
</div>

<style>
#calendar {
  font-size: 1.2rem;
  min-height: 600px;
}
</style>


<!-- Modal Crear Turno -->
<div class="modal fade" id="crearTurnoModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="formTurno" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Fecha</label>
          <input type="date" name="fecha" id="fecha" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Hora</label>
          <input type="time" name="hora" id="hora" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
    events: 'get_turnos.php', // 🔴 usa el mismo endpoint que el usuario
    dateClick: function(info) {
      // Abrir modal con fecha seleccionada
      document.getElementById('fecha').value = info.dateStr;
      var modal = new bootstrap.Modal(document.getElementById('crearTurnoModal'));
      modal.show();
    }
  });
  calendar.render();

  // Manejo de formulario
  document.getElementById('formTurno').addEventListener('submit', function(e) {
    e.preventDefault();
    const fecha = document.getElementById('fecha').value;
    const hora = document.getElementById('hora').value;

    fetch('agregar_turno.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ fecha, hora })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
        calendar.refetchEvents(); // 🔄 recargar eventos en el calendario
        bootstrap.Modal.getInstance(document.getElementById('crearTurnoModal')).hide();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch(err => alert("Error en la solicitud: " + err));
  });
});
</script>

</body>
</html>
