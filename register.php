<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nombre = $_POST['nombre'];

    $newRef = $database->getReference('usuarios')->push([
        'email' => $email,
        'password' => $password,
        'nombre' => $nombre,
        'rol' => 'USER'
    ]);

    $_SESSION['user'] = [
        'id' => $newRef->getKey(),
        'rol' => 'USER',
        'nombre' => $nombre
    ];
    header("Location: user/agenda.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro - Agenda Virtual</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow p-4" style="width: 100%; max-width: 420px;">
    <h2 class="text-center mb-3">Crear cuenta</h2>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-success w-100">Registrarse</button>

      <div class="mt-3 text-center">
        <a href="login.php" class="btn btn-outline-secondary w-100">Ya tengo cuenta</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
