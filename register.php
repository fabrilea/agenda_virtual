<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $newUser = [
        'nombre' => $nombre,
        'email' => $email,
        'password' => $password,
        'rol' => 'USER'
    ];

    $database->getReference('usuarios')->push($newUser);
    header("Location: login.php");
}
?>
<form method="POST">
    Nombre: <input type="text" name="nombre" required><br>
    Email: <input type="email" name="email" required><br>
    Contraseña: <input type="password" name="password" required><br>
    <button type="submit">Registrarse</button>
</form>
