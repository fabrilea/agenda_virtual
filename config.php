<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

$firebaseUrl = getenv('FIREBASE_URL');

// Usa el archivo copiado a /tmp (con permisos 644)
$firebaseKeyPath = getenv('FIREBASE_KEY_PATH') ?: '/tmp/firebase_credentials.json';
if (!is_readable($firebaseKeyPath)) {
    error_log("⚠️ No se puede leer el archivo de credenciales en $firebaseKeyPath");
}

$factory = (new Factory)
    ->withDatabaseUri($firebaseUrl)
    ->withServiceAccount($firebaseKeyPath);

$database = $factory->createDatabase();
?>
