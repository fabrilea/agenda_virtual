<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

$firebaseUrl = getenv('FIREBASE_URL');
$firebaseKeyPath = getenv('FIREBASE_KEY_PATH');

$factory = (new Factory)
    ->withDatabaseUri($firebaseUrl)
    ->withServiceAccount($firebaseKeyPath);

$database = $factory->createDatabase();
?>
