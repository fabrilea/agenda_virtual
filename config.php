<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

// ✅ Copiar el secreto de Render a un archivo temporal legible
$source = '/etc/secrets/firebase_credentials.json';
$tmp = '/tmp/firebase_credentials.json';

if (file_exists($source)) {
    if (!@copy($source, $tmp)) {
        error_log("❌ No se pudo copiar $source a $tmp");
    } else {
        @chmod($tmp, 0644);
        error_log("✅ Copiado correctamente a $tmp");
    }
} else {
    error_log("⚠️ El archivo $source no existe");
}

$firebaseUrl = getenv('FIREBASE_URL');

// ✅ Usamos la copia temporal como service account
$firebaseKeyPath = $tmp;

if (!is_readable($firebaseKeyPath)) {
    error_log("❌ El archivo $firebaseKeyPath no es legible");
}

$factory = (new Factory)
    ->withDatabaseUri($firebaseUrl)
    ->withServiceAccount($firebaseKeyPath);

$database = $factory->createDatabase();
?>
