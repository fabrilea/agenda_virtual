<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

// ✅ Copiamos el secreto de /etc/secrets a /tmp antes de usarlo
$source = '/etc/secrets/firebase_credentials.json';
$tmp = '/tmp/firebase_credentials.json';
if (file_exists($source)) {
    @copy($source, $tmp);
    @chmod($tmp, 0644);
}

$firebaseUrl = getenv('FIREBASE_URL');
$firebaseKeyPath = $tmp;

$factory = (new Factory)
    ->withDatabaseUri($firebaseUrl)
    ->withServiceAccount($firebaseKeyPath);

$database = $factory->createDatabase();
?>
