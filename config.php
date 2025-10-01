<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

// URL del RTDB (no uses la de la consola)
$dbUri = 'https://agenda-31b98-default-rtdb.firebaseio.com';

// Guardar credenciales en archivo temporal (Railway → variable FIREBASE_CREDENTIALS)
$credenciales = getenv('FIREBASE_CREDENTIALS');
$tmpPath = sys_get_temp_dir().'/firebase.json';
file_put_contents($tmpPath, $credenciales);

// Inicializar Firebase
$firebase = (new Factory)
    ->withServiceAccount($tmpPath)
    ->withDatabaseUri($dbUri);

$database = $firebase->createDatabase();
