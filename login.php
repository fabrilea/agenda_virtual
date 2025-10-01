<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $usuarios = $database->getReference('usuarios')->getValue() ?: [];
    foreach ($usuarios as $uid => $user) {
        if (!isset($user['email'], $user['password'], $user['rol'])) continue;

        if ($user['email'] === $email && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $uid,
                'rol' => $user['rol'],
                'nombre' => $user['nombre'] ?? 'Usuario'
            ];
            header("Location: " . ($user['rol'] === 'ADMIN' ? "admin/panel.php" : "user/agenda.php"));
            exit;
        }
    }
    $error = "Credenciales inválidas";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Agenda Virtual</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
    <h2 class="text-center mb-3">Iniciar sesión</h2>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input id="email" type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input id="password" type="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Ingresar</button>

      <div class="mt-3 text-center">
        <a href="register.php" class="btn btn-outline-secondary w-100">Registrarse</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
