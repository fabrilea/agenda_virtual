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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<?php include '../sidebar.php'; ?>

<div class="container-fluid py-3">
  <h2 class="mb-4 text-center">⚙️ Panel de Administración</h2>
  <p class="mb-4 text-center">Bienvenido, <strong><?= htmlspecialchars($_SESSION['user']['nombre']) ?></strong></p>

  <div class="row g-3">
    <!-- Cada tarjeta ocupa ancho completo en celu, mitad en tablet -->
    <div class="col-12 col-md-6">
      <div class="card shadow-sm border-0 h-100 text-center p-3">
        <h5>👥 Usuarios</h5>
        <p class="display-6 fw-bold text-primary"><?= $totalUsuarios ?></p>
        <a href="usuarios.php" class="btn btn-primary btn-lg w-100">Gestionar</a>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <div class="card shadow-sm border-0 h-100 text-center p-3">
        <h5>📅 Reservados</h5>
        <p class="display-6 fw-bold text-success"><?= $reservados ?></p>
        <a href="turnos.php" class="btn btn-success btn-lg w-100">Ver turnos</a>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <div class="card shadow-sm border-0 h-100 text-center p-3">
        <h5>✅ Disponibles</h5>
        <p class="display-6 fw-bold text-info"><?= $disponibles ?></p>
        <a href="turnos.php" class="btn btn-info btn-lg w-100">Administrar</a>
      </div>
    </div>
  </div>
</div>


</body>
</html>
