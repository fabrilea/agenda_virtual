<?php
session_start();
require 'config.php'; // 👈 conexión Firebase

// Procesar login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $usuarios = $database->getReference('usuarios')->getValue() ?: [];
    $error = "Credenciales inválidas";

    foreach ($usuarios as $uid => $user) {
        if ($user['email'] === $email && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $uid,
                'rol' => $user['rol'],
                'nombre' => $user['nombre']
            ];

            // Redirigir según rol
            if ($user['rol'] === "ADMIN") {
                header("Location: admin/panel.php");
            } else {
                header("Location: user/agenda.php");
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/styles.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .login-card {
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

<div class="card shadow login-card">
  <h2 class="text-center mb-4">🔑 Iniciar Sesión</h2>
  
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger text-center"><?= $error ?></div>
  <?php endif; ?>

  <!-- 🔹 OJO: ahora action="" para postear a sí misma -->
  <form method="POST" action="">
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
             autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Ingresar</button>
  </form>
  <div class="mt-3">
    <a href="register.php" class="btn btn-outline-secondary btn-lg w-100">Registrarse</a>
  </div>
</div>

</body>
</html>
