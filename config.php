<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

$firebaseCredentials = getenv('FIREBASE_CREDENTIALS');
$firebaseUrl = getenv('FIREBASE_URL');

// 🧩 Validar que ambas existan
if (empty($firebaseCredentials)) {
    die('❌ ERROR: Falta la variable FIREBASE_CREDENTIALS');
}
if (empty($firebaseUrl)) {
    die('❌ ERROR: Falta la variable FIREBASE_URL');
}

// ✅ Guardar el JSON en un archivo temporal (solo lectura por el propietario)
$tmpPath = '/tmp/firebase_credentials_' . getmypid() . '.json';
file_put_contents($tmpPath, $firebaseCredentials);
chmod($tmpPath, 0600);

// 🔧 Inicializar Firebase
$factory = (new Factory)
    ->withServiceAccount($tmpPath)
    ->withDatabaseUri($firebaseUrl);

$database = $factory->createDatabase();

// 🔒 Eliminar credenciales del disco inmediatamente
unlink($tmpPath);
?>
