<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    header("Location: ../login.php");
    exit;
}
require '../security_headers.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Turnos</title>
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
    .form-control, .btn {
      font-size: 1.2rem;
      padding: 0.9rem;
    }
  </style>
</head>
<body class="bg-light">

<?php include '../sidebar.php'; ?>

<div class="container-fluid py-3">
  <h2 class="mb-4 text-center">📅 Gestión de Turnos</h2>

  <!-- Botón siempre visible en mobile -->
  <div class="d-flex justify-content-end mb-2">
    <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#crearTurnoModal">
      ➕ Nuevo Turno
    </button>
  </div>

  <div class="card shadow p-4">
    <div id="calendar"></div>
  </div>
</div>

<!-- Modal Crear Turno -->
<div class="modal fade" id="crearTurnoModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="formTurno" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">➕ Agregar Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label" for="fecha">Fecha</label>
          <input type="date" name="fecha" id="fecha" class="form-control form-control-lg" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="hora">Hora</label>
          <input type="time" name="hora" id="hora" class="form-control form-control-lg" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success btn-lg">Guardar</button>
        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Acciones sobre Turno -->
<div class="modal fade" id="accionTurnoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="accionTurnoTitulo">Gestionar Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="accionTurnoBody">
        <p class="mb-0" id="accionTurnoInfo"></p>
      </div>
      <div class="modal-footer" id="accionTurnoBotones">
        <!-- Botones inyectados según estado -->
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var initialView = window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth';

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: initialView,
    locale: 'es',
    height: "auto",
    events: 'get_turnos.php',
    headerToolbar: {
      left: 'prev,next',
      center: 'title',
      right: 'today'
    },
    buttonText: { today: 'Hoy' },

    // ── Clic en un evento del calendario ──────────────────────
    eventClick: function(info) {
      const ev     = info.event;
      const id     = ev.id;
      const estado = ev.extendedProps.estado || '';
      const fecha  = ev.startStr.split('T')[0];
      const hora   = ev.startStr.split('T')[1] ? ev.startStr.split('T')[1].slice(0,5) : '';

      const titulo  = document.getElementById('accionTurnoTitulo');
      const infoEl  = document.getElementById('accionTurnoInfo');
      const botones = document.getElementById('accionTurnoBotones');

      titulo.textContent = ev.title;
      infoEl.innerHTML   = `<strong>Fecha:</strong> ${fecha} &nbsp; <strong>Hora:</strong> ${hora}
                             <br><strong>Estado:</strong> <span class="badge" style="background:${ev.backgroundColor}">${estado}</span>`;
      botones.innerHTML  = '';

      // Botón CONFIRMAR (solo si está PENDIENTE o RESERVADO legacy)
      if (estado === 'PENDIENTE' || estado === 'RESERVADO') {
        const btnConfirmar = document.createElement('button');
        btnConfirmar.className = 'btn btn-success btn-lg';
        btnConfirmar.textContent = '✅ Confirmar';
        btnConfirmar.addEventListener('click', () => accionTurno('confirmar', id, calendar));
        botones.appendChild(btnConfirmar);
      }

      // Botón CANCELAR (no mostrar si ya está CANCELADO o DISPONIBLE sin usuario)
      if (['PENDIENTE', 'CONFIRMADO', 'RESERVADO'].includes(estado)) {
        const btnCancelar = document.createElement('button');
        btnCancelar.className = 'btn btn-danger btn-lg';
        btnCancelar.textContent = '❌ Cancelar turno';
        btnCancelar.addEventListener('click', () => accionTurno('cancelar', id, calendar));
        botones.appendChild(btnCancelar);
      }

      // Botón cerrar siempre visible
      const btnCerrar = document.createElement('button');
      btnCerrar.className = 'btn btn-secondary btn-lg';
      btnCerrar.setAttribute('data-bs-dismiss', 'modal');
      btnCerrar.textContent = 'Cerrar';
      botones.appendChild(btnCerrar);

      bootstrap.Modal.getOrCreateInstance(document.getElementById('accionTurnoModal')).show();
    }
  });

  calendar.render();

  // ── Formulario nuevo turno ────────────────────────────────
  document.getElementById('formTurno').addEventListener('submit', function(e) {
    e.preventDefault();
    const fecha = document.getElementById('fecha').value;
    const hora  = document.getElementById('hora').value;

    fetch('agregar_turno.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ fecha, hora })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        calendar.refetchEvents();
        bootstrap.Modal.getInstance(document.getElementById('crearTurnoModal')).hide();
      }
    })
    .catch(err => alert("Error en la solicitud: " + err));
  });

  // ── Acción confirmar / cancelar ──────────────────────────
  function accionTurno(accion, id, cal) {
    const endpoint = accion === 'confirmar' ? 'confirmar_turno.php' : 'cancelar_turno.php';
    const confirmMsg = accion === 'confirmar'
      ? '¿Confirmar este turno? Se notificará al usuario por email.'
      : '¿Cancelar este turno? Se notificará al usuario por email.';

    if (!confirm(confirmMsg)) return;

    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        cal.refetchEvents();
        bootstrap.Modal.getInstance(document.getElementById('accionTurnoModal')).hide();
      }
    })
    .catch(err => alert("Error en la solicitud: " + err));
  }
});
</script>

</body>
</html>
