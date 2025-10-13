<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

// 🔥 Leer las credenciales directamente desde la variable de entorno
$firebaseCredentials = getenv('FIREBASE_CREDENTIALS');
$firebaseUrl = getenv('FIREBASE_URL');

// 🧩 Validaciones básicas
if (empty($firebaseCredentials)) {
    die('❌ ERROR: La variable FIREBASE_CREDENTIALS no está configurada.');
}
if (empty($firebaseUrl)) {
    die('❌ ERROR: La variable FIREBASE_URL no está configurada.');
}

// ✅ Crear un archivo temporal con las credenciales
$tmpPath = '/tmp/firebase_credentials.json';
file_put_contents($tmpPath, $firebaseCredentials);
chmod($tmpPath, 0644);

// 🔧 Inicializar Firebase con el archivo temporal
$factory = (new Factory)
    ->withServiceAccount($tmpPath)
    ->withDatabaseUri($firebaseUrl);

$database = $factory->createDatabase();
?>
