<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// En production : copier config/local.example.php en config/local.php et mettre les accès MySQL de l'hébergeur.
$local = file_exists(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];

$cfg = array_merge([
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'database' => getenv('DB_NAME') ?: 'social_secret_game',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
], $local);

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $cfg['host'],
    'database'  => $cfg['database'],
    'username'  => $cfg['username'],
    'password'  => $cfg['password'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
