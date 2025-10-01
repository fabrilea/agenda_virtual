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
  <title>Panel de Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<?php include '../sidebar.php'; ?>

<div class="container py-4">
  <h2 class="mb-4">Panel de Administración</h2>
  <p class="mb-4">Bienvenido, <strong><?= htmlspecialchars($_SESSION['user']['nombre']) ?></strong></p>

  <div class="row g-4">
    <div class="col-12 col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title">👥 Usuarios</h5>
          <p class="display-6 fw-bold text-primary">120</p>
          <a href="usuarios.php" class="btn btn-outline-primary btn-sm">Gestionar</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title">📅 Reservados</h5>
          <p class="display-6 fw-bold text-success">45</p>
          <a href="turnos.php" class="btn btn-outline-success btn-sm">Ver turnos</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title">✅ Disponibles</h5>
          <p class="display-6 fw-bold text-info">30</p>
          <a href="turnos.php" class="btn btn-outline-info btn-sm">Administrar</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title">❌ Cancelados</h5>
          <p class="display-6 fw-bold text-danger">10</p>
          <a href="turnos.php" class="btn btn-outline-danger btn-sm">Historial</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-8">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <h5 class="card-title mb-3">📊 Resumen General</h5>
          <p>Aquí podrás agregar gráficos o estadísticas.</p>
          <ul>
            <li>Total de turnos creados</li>
            <li>Porcentaje de asistencia</li>
            <li>Usuarios más activos</li>
          </ul>
          <a href="reportes.php" class="btn btn-outline-dark btn-sm">Ver reportes</a>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
