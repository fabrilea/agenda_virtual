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
  <div class="card shadow p-4">
    <h2 class="mb-4">Agenda de <?= htmlspecialchars($_SESSION['user']['nombre']) ?></h2>
    <div id="calendar"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    events: 'get_turnos.php', // 🔴 respeté tu link original
    locale: 'es'
  });
  calendar.render();
});
</script>

</body>
</html>
