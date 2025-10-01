<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $adminKey = $_POST['adminKey'] ?? null;

    $usuarios = $database->getReference('usuarios')->getValue();

    foreach ($usuarios as $uid => $user) {
        if ($user['email'] === $email && password_verify($password, $user['password'])) {
            if ($user['rol'] === "ADMIN") {
                $claveReal = "CLAVE_SECRETA"; // 🔑 podés moverla a Firebase o ENV
                if ($adminKey === $claveReal) {
                    $_SESSION['user'] = ['id' => $uid, 'rol' => 'ADMIN', 'nombre' => $user['nombre']];
                    header("Location: admin/panel.php");
                    exit;
                } else {
                    echo "Clave admin incorrecta";
                }
            } else {
                $_SESSION['user'] = ['id' => $uid, 'rol' => 'USER', 'nombre' => $user['nombre']];
                header("Location: user/agenda.php");
                exit;
            }
        }
    }

    echo "Credenciales inválidas";
}
?>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Contraseña: <input type="password" name="password" required><br>
    (Solo admin) Clave extra: <input type="password" name="adminKey"><br>
    <button type="submit">Ingresar</button>
</form>
