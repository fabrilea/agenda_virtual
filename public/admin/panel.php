<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    header("Location: ../login.php");
    exit;
}

require '../config.php';

$usuarios = $database->getReference('usuarios')->getValue() ?: [];
$turnos   = $database->getReference('turnos')->getValue() ?: [];

$totalUsuarios = count($usuarios);
$totalTurnos   = count($turnos);
$reservados    = count(array_filter($turnos, fn($t) => $t['estado'] === 'RESERVADO'));
$disponibles   = count(array_filter($turnos, fn($t) => $t['estado'] === 'DISPONIBLE'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    .card { border-radius: 1rem; }
    .card h5 { font-size: 1.3rem; }
    .display-6 { font-size: 2.5rem; }
    .btn { font-size: 1.2rem; padding: 0.8rem; }
  </style>
</head>
<body class="bg-light">

<?php include '../sidebar.php'; ?>

<div class="container-fluid py-3">
  <h2 class="mb-4 text-center">⚙️ Panel de Administración</h2>
  <p class="mb-4 text-center">Bienvenido, <strong><?= htmlspecialchars($_SESSION['user']['nombre']) ?></strong></p>

  <div class="row g-3">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card shadow text-center p-3">
        <h5>👥 Usuarios</h5>
        <p class="display-6 text-primary fw-bold"><?= $totalUsuarios ?></p>
        <a href="usuarios.php" class="btn btn-primary w-100 btn-lg">Gestionar</a>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card shadow text-center p-3">
        <h5>📅 Reservados</h5>
        <p class="display-6 text-success fw-bold"><?= $reservados ?></p>
        <a href="turnos.php" class="btn btn-success w-100 btn-lg">Ver turnos</a>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card shadow text-center p-3">
        <h5>✅ Disponibles</h5>
        <p class="display-6 text-info fw-bold"><?= $disponibles ?></p>
        <a href="turnos.php" class="btn btn-info w-100 btn-lg">Administrar</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
