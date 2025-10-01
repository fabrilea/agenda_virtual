<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== "ADMIN") {
    header("Location: ../login.php");
    exit;
}

// 🔹 Operaciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $rol = $_POST['rol'];

        $database->getReference('usuarios')->push([
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password,
            'rol' => $rol
        ]);
    }

    if ($accion === 'editar') {
        $uid = $_POST['id'];
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $rol = $_POST['rol'];

        $update = [
            'nombre' => $nombre,
            'email' => $email,
            'rol' => $rol
        ];

        if (!empty($_POST['password'])) {
            $update['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $database->getReference('usuarios/'.$uid)->update($update);
    }

    if ($accion === 'eliminar') {
        $uid = $_POST['id'];
        $database->getReference('usuarios/'.$uid)->remove();
    }

    header("Location: usuarios.php");
    exit;
}

// 🔹 Obtener usuarios
$usuarios = $database->getReference('usuarios')->getValue() ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<?php include '../sidebar.php'; ?>

<div class="container py-4">
  <h2 class="mb-4">Gestión de Usuarios</h2>

  <!-- Formulario Agregar Usuario -->
  <div class="card mb-4 shadow">
    <div class="card-body">
      <h5 class="card-title">➕ Agregar Usuario</h5>
      <form method="POST">
        <input type="hidden" name="accion" value="agregar">

        <div class="row g-3">
          <div class="col-md-3">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
          </div>
          <div class="col-md-3">
            <input type="email" name="email" class="form-control" placeholder="Correo" required>
          </div>
          <div class="col-md-3">
            <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
          </div>
          <div class="col-md-2">
            <select name="rol" class="form-select" required>
              <option value="USER">USER</option>
              <option value="ADMIN">ADMIN</option>
            </select>
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-success w-100">Guardar</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabla de Usuarios -->
  <div class="card shadow">
    <div class="card-body">
      <h5 class="card-title">👥 Usuarios Registrados</h5>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
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
                  <!-- Botón Editar (abre modal) -->
                  <button class="btn btn-sm btn-warning" 
                          data-bs-toggle="modal" 
                          data-bs-target="#editarModal<?= $uid ?>">Editar</button>

                  <!-- Botón Eliminar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= $uid ?>">
                    <button type="submit" class="btn btn-sm btn-danger"
                      onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
                      Eliminar
                    </button>
                  </form>
                </td>
              </tr>

              <!-- Modal Editar -->
              <div class="modal fade" id="editarModal<?= $uid ?>" tabindex="-1">
                <div class="modal-dialog">
                  <form method="POST" class="modal-content">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id" value="<?= $uid ?>">

                    <div class="modal-header">
                      <h5 class="modal-title">Editar Usuario</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" 
                               value="<?= htmlspecialchars($u['nombre'] ?? '') ?>" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($u['email'] ?? '') ?>" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select">
                          <option value="USER" <?= ($u['rol'] ?? '') === 'USER' ? 'selected' : '' ?>>USER</option>
                          <option value="ADMIN" <?= ($u['rol'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Nueva contraseña (opcional)</label>
                        <input type="password" name="password" class="form-control">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-primary">Guardar cambios</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</body>
</html>
