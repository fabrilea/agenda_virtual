<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head><title>Agenda Virtual</title></head>
<link rel="stylesheet" href="css/styles.css">
<body>
    <h1>Bienvenido a la Agenda Virtual</h1>
    <?php if (!isset($_SESSION['user'])): ?>
        <a href="login.php">Iniciar Sesión</a> | 
        <a href="register.php">Registrarse</a>
    <?php else: ?>
        <p>Hola, <?= htmlspecialchars($_SESSION['user']['nombre']) ?> (<?= $_SESSION['user']['rol'] ?>)</p>

        <?php if ($_SESSION['user']['rol'] === 'USER'): ?>
            <a href="user/agenda.php">Ir a mi agenda</a>
        <?php elseif ($_SESSION['user']['rol'] === 'ADMIN'): ?>
            <a href="admin/panel.php">Ir al panel admin</a>
        <?php endif; ?>

        <br><a href="logout.php">Cerrar sesión</a>
    <?php endif; ?>
</body>
</html>
