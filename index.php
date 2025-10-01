<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agenda Virtual</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-light">

<div class="container py-5 text-center">
  <h1 class="mb-4">Agenda Virtual</h1>

  <?php if (!isset($_SESSION['user'])): ?>
      <a href="login.php" class="btn btn-primary btn-lg me-2">Iniciar Sesión</a>
      <a href="register.php" class="btn btn-success btn-lg">Registrarse</a>
  <?php else: ?>
      <p class="mb-4">Hola, <strong><?= htmlspecialchars($_SESSION['user']['nombre']) ?></strong> 
      (<?= $_SESSION['user']['rol'] ?>)</p>

      <?php if ($_SESSION['user']['rol'] === 'USER'): ?>
          <a href="user/agenda.php" class="btn btn-outline-primary btn-lg">Ir a mi agenda</a>
      <?php elseif ($_SESSION['user']['rol'] === 'ADMIN'): ?>
          <a href="admin/panel.php" class="btn btn-outline-danger btn-lg">Ir al panel admin</a>
      <?php endif; ?>

      <div class="mt-4">
        <a href="logout.php" class="btn btn-secondary">Cerrar sesión</a>
      </div>
  <?php endif; ?>
</div>

</body>
</html>
