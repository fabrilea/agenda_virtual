<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/styles.css" rel="stylesheet">
  <style>
    .login-card {
      max-width: 500px;
      width: 100%;
      margin: auto;
      padding: 2rem;
      font-size: 1.2rem;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">

<div class="card shadow login-card">
  <h2 class="text-center mb-4">🔑 Iniciar Sesión</h2>
  <form method="POST" action="">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control form-control-lg" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Contraseña</label>
      <input type="password" name="password" class="form-control form-control-lg" required>
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Ingresar</button>
  </form>
  <div class="mt-3">
    <a href="register.php" class="btn btn-outline-secondary btn-lg w-100">Registrarse</a>
  </div>
</div>

</body>
</html>
