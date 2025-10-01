<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    header("Location: ../login.php");
    exit;
}

require '../config.php';

// 🔹 Leer datos de Firebase
$usuarios = $database->getReference('usuarios')->getValue() ?: [];
$turnos   = $database->getReference('turnos')->getValue() ?: [];

// 🔹 Calcular métricas
$totalUsuarios = count($usuarios);
$totalTurnos   = count($turnos);

$reservados  = count(array_filter($turnos, fn($t) => $t['estado'] === 'RESERVADO'));
$disponibles = count(array_filter($turnos, fn($t) => $t['estado'] === 'DISPONIBLE'));
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
    <!-- Usuarios -->
    <div class="col-12 col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title">👥 Usuarios</h5>
          <p class="display-6 fw-bold text-primary"><?= $totalUsuarios ?></p>
          <a href="usuarios.php" class="btn btn-outline-primary btn-sm">Gestionar</a>
        </div>
      </div>
    </div>

    <!-- Reservados -->
    <div class="col-12 col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title">📅 Reservados</h5>
          <p class="display-6 fw-bold text-success"><?= $reservados ?></p>
          <a href="turnos.php" class="btn btn-outline-success btn-sm">Ver turnos</a>
        </div>
      </div>
    </div>

    <!-- Disponibles -->
    <div class="col-12 col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title">✅ Disponibles</h5>
          <p class="display-6 fw-bold text-info"><?= $disponibles ?></p>
          <a href="turnos.php" class="btn btn-outline-info btn-sm">Administrar</a>
        </div>
      </div>
    </div>

    <!-- Resumen -->
    <div class="col-12 col-md-8">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <h5 class="card-title mb-3">📊 Resumen General</h5>
          <ul>
            <li>Total de turnos creados: <?= $totalTurnos ?></li>
            <li>Reservados: <?= $reservados ?></li>
            <li>Disponibles: <?= $disponibles ?></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
