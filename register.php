<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrarse</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/styles.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .register-card {
      max-width: 520px;
      width: 100%;
      margin: auto;
      padding: 2rem;
      border-radius: 1rem;
      font-size: 1.2rem;
    }
    .form-control, .btn {
      font-size: 1.2rem;
      padding: 0.9rem;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="card shadow register-card">
  <h2 class="text-center mb-4">📝 Registro</h2>
  <form method="POST" action="register.php">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" id="nombre" name="nombre" 
             class="form-control form-control-lg" required 
             autocomplete="name">
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Correo electrónico</label>
      <input type="email" id="email" name="email" 
             class="form-control form-control-lg" required 
             autocomplete="email">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input type="password" id="password" name="password" 
             class="form-control form-control-lg" required 
             autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-success btn-lg w-100">Crear cuenta</button>
  </form>
  <div class="mt-3">
    <a href="login.php" class="btn btn-outline-secondary btn-lg w-100">Volver a iniciar sesión</a>
  </div>
</div>

</body>
</html>
