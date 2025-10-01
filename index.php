<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agenda Virtual</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="card shadow text-center" style="max-width:500px;width:100%;">
  <h1 class="mb-4">Agenda Virtual</h1>
  <?php if (!isset($_SESSION['user'])): ?>
      <a href="login.php" class="btn btn-primary w-100 mb-2">Iniciar Sesión</a>
      <a href="register.php" class="btn btn-outline-secondary w-100">Registrarse</a>
  <?php else: ?>
      <p>Hola, <?= htmlspecialchars($_SESSION['user']['nombre']) ?> (<?= $_SESSION['user']['rol'] ?>)</p>
      <?php if ($_SESSION['user']['rol'] === 'USER'): ?>
          <a href="user/agenda.php" class="btn btn-success w-100 mb-2">Ir a mi agenda</a>
      <?php else: ?>
          <a href="admin/panel.php" class="btn btn-warning w-100 mb-2">Ir al panel admin</a>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-danger w-100">Cerrar sesión</a>
  <?php endif; ?>
</div>

</body>
</html>
