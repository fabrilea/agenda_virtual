<?php
session_start();

require 'security_headers.php'; // 🛡️ Cabeceras de seguridad HTTP
require '../config.php'; // 👈 conexión Firebase

// 🔐 Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Solicitud inválida.');
    }

    $nombre   = trim($_POST['nombre']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $telefono = trim($_POST['telefono'] ?? ''); // Opcional: para notificaciones WhatsApp

    // Validaciones server-side
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } else {
        try {
            // Verificar email duplicado
            $usuarios = $database->getReference('usuarios')->getValue() ?: [];
            foreach ($usuarios as $u) {
                if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
                    $error = "Ya existe una cuenta con ese correo electrónico";
                    break;
                }
            }

            if (empty($error)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Teléfono: solo dígitos y + (opcional)
                $telefonoLimpio = !empty($telefono) ? preg_replace('/[^\d+]/', '', $telefono) : '';

                $database->getReference('usuarios')->push([
                    'nombre'   => $nombre,
                    'email'    => $email,
                    'password' => $hash,
                    'telefono' => $telefonoLimpio,
                    'rol'      => 'USER' // siempre USER en auto-registro
                ]);
                header("Location: login.php?registro=ok");
                exit;
            }
        } catch (\Throwable $e) {
            $error = "Error al crear la cuenta. Intente de nuevo.";
        }
    }
}
?>
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

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre completo</label>
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
             class="form-control form-control-lg" required minlength="8"
             autocomplete="new-password">
      <div class="form-text">Mínimo 8 caracteres.</div>
    </div>
    <div class="mb-3">
      <label for="telefono" class="form-label">Teléfono <small class="text-muted">(opcional, para WhatsApp)</small></label>
      <input type="tel" id="telefono" name="telefono"
             class="form-control form-control-lg"
             placeholder="Ej: +5491112345678"
             autocomplete="tel">
      <div class="form-text">Con código de país. Ej: +54 9 11 1234-5678</div>
    </div>
    <button type="submit" class="btn btn-success btn-lg w-100">Crear cuenta</button>
  </form>

  <div class="mt-3">
    <a href="login.php" class="btn btn-outline-secondary btn-lg w-100">Volver al inicio de sesión</a>
  </div>
</div>

</body>
</html>
