<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

// URL de Firebase
$dbUri = getenv('FIREBASE_DB_URI');

// Guardar JSON de credenciales en archivo temporal
$credenciales = getenv('FIREBASE_CREDENTIALS');
$tmpPath = sys_get_temp_dir().'/firebase.json';
file_put_contents($tmpPath, $credenciales);

// Inicializar Firebase
$firebase = (new Factory)
    ->withServiceAccount($tmpPath)
    ->withDatabaseUri($dbUri);

$database = $firebase->createDatabase();
