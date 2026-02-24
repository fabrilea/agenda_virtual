<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    header("Location: ../login.php");
    exit;
}
require '../security_headers.php';
require '../../config.php';

// 🔐 Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$ROL_VALIDOS = ['USER', 'ADMIN'];
$error_form  = '';

// Operaciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Solicitud inválida.');
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $nombre   = trim($_POST['nombre']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $rol      = $_POST['rol']           ?? '';

        if (!$nombre || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $error_form = "Datos inválidos: nombre requerido, email válido y contraseña mínimo 8 caracteres.";
        } elseif (!in_array($rol, $ROL_VALIDOS, true)) {
            $error_form = "Rol inválido.";
        } else {
            try {
                // Verificar email duplicado
                $existentes = $database->getReference('usuarios')->getValue() ?: [];
                $duplicado  = false;
                foreach ($existentes as $u) {
                    if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
                        $duplicado = true;
                        break;
                    }
                }
                if ($duplicado) {
                    $error_form = "Ya existe un usuario con ese correo electrónico.";
                } else {
                    $database->getReference('usuarios')->push([
                        'nombre'   => $nombre,
                        'email'    => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'rol'      => $rol
                    ]);
                    header("Location: usuarios.php");
                    exit;
                }
            } catch (\Throwable $e) {
                $error_form = "Error al agregar el usuario.";
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = $_POST['id'] ?? '';

        // Prevenir path traversal: solo caracteres alfanuméricos y guiones
        if (!preg_match('/^[\w-]+$/', $id)) {
            http_response_code(400);
            die('ID inválido.');
        }
        // Impedir que el admin se elimine a sí mismo
        if ($id === $_SESSION['user']['id']) {
            $error_form = "No puedes eliminar tu propia cuenta.";
        } else {
            try {
                $database->getReference('usuarios/'.$id)->remove();
                header("Location: usuarios.php");
                exit;
            } catch (\Throwable $e) {
                $error_form = "Error al eliminar el usuario.";
            }
        }
    }
}

try {
    $usuarios = $database->getReference('usuarios')->getValue() ?: [];
} catch (\Throwable $e) {
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    .form-control, .form-select, .btn { font-size: 1.2rem; padding: 0.8rem; }
    .table th, .table td { font-size: 1.1rem; padding: 1rem; }
    .card { border-radius: 1rem; }
  </style>
</head>
<body class="bg-light">

<?php include '../sidebar.php'; ?>

<div class="container-fluid py-3">
  <h2 class="mb-4 text-center">👥 Gestión de Usuarios</h2>

  <?php if (!empty($error_form)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_form, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <!-- Formulario Agregar -->
  <div class="card mb-4 shadow p-3">
    <h5>➕ Agregar Usuario</h5>
    <form method="POST" class="row g-3">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="accion" value="agregar">

      <div class="col-12 col-md-3">
        <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
      </div>
      <div class="col-12 col-md-3">
        <input type="email" name="email" class="form-control" placeholder="Correo" required>
      </div>
      <div class="col-12 col-md-3">
        <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
      </div>
      <div class="col-12 col-md-2">
        <select name="rol" class="form-select" required>
          <option value="USER">USER</option>
          <option value="ADMIN">ADMIN</option>
        </select>
      </div>
      <div class="col-12 col-md-1">
        <button type="submit" class="btn btn-success w-100">✔</button>
      </div>
    </form>
  </div>

  <!-- Tabla Usuarios -->
  <div class="card shadow p-3">
    <h5>📋 Usuarios Registrados</h5>
    <div class="table-responsive">
      <table class="table table-striped align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $uid => $u): ?>
            <tr>
              <td><?= htmlspecialchars($uid) ?></td>
              <td><?= htmlspecialchars($u['nombre'] ?? '-') ?></td>
              <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
              <td>
                <span class="badge <?= ($u['rol'] ?? '') === 'ADMIN' ? 'bg-danger' : 'bg-primary' ?>">
                  <?= htmlspecialchars($u['rol'] ?? '-') ?>
                </span>
              </td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($uid, ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="btn btn-sm btn-danger"
                          onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
                    🗑 Eliminar
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
