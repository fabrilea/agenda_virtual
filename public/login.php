<?php
session_start();

require 'security_headers.php'; // 🛡️ Cabeceras de seguridad HTTP
require '../config.php'; // 👈 conexión Firebase

// 🔐 Generar token CSRF si no existe en la sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// Procesar login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Solicitud inválida.');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        $error = "Datos inválidos";
    } else {
        try {
            $usuarios = $database->getReference('usuarios')->getValue() ?: [];
            $error = "Credenciales inválidas";

            foreach ($usuarios as $uid => $user) {
                if (isset($user['email'], $user['password'])
                    && $user['email'] === $email
                    && password_verify($password, $user['password'])) {

                    // 🔄 Regenerar ID de sesión para prevenir session fixation
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // nuevo token post-login

                    $_SESSION['user'] = [
                        'id'       => $uid,
                        'rol'      => $user['rol'],
                        'nombre'   => $user['nombre'],
                        'email'    => $user['email']    ?? '',
                        'telefono' => $user['telefono'] ?? '',
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
        } catch (\Throwable $e) {
            $error = "Error al iniciar sesión. Inténtelo de nuevo.";
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
    <div class="alert alert-danger text-center"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
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
