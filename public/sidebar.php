<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="../index.php">Agenda Virtual</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="sidebarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php
          $rolActual = isset($_SESSION['user']['rol']) ? $_SESSION['user']['rol'] : '';
          if ($rolActual === 'USER'):
        ?>
          <li class="nav-item">
            <a class="nav-link" href="../user/agenda.php">📅 Mi Agenda</a>
          </li>
        <?php elseif ($rolActual === 'ADMIN'): ?>
          <li class="nav-item">
            <a class="nav-link" href="../admin/panel.php">⚙️ Panel Admin</a>
          </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link" href="../index.php">🏠 Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="../logout.php">🚪 Cerrar sesión</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
