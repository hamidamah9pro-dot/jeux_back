<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

// Récupération de l'URL de connexion Supabase (depuis l'environnement Render ou valeur de secours pour le local)
$databaseUrl = getenv('DATABASE_URL') ?: $_ENV['DATABASE_URL'] ?? 'postgresql://postgres:TON_MOT_DE_PASSE@db.TON_REF_PROJET.supabase.co:5432/postgres';

// Analyse de l'URL pour extraire les différents paramètres de connexion
$parsedUrl = parse_url($databaseUrl);

$host = $parsedUrl['host'] ?? 'localhost';
$port = $parsedUrl['port'] ?? 5432;
$database = ltrim($parsedUrl['path'] ?? '', '/');
$username = $parsedUrl['user'] ?? 'postgres';
$password = $parsedUrl['pass'] ?? '';

$capsule->addConnection([
    'driver'    => 'pgsql',
    'host'      => $host,
    'port'      => $port,
    'database'  => $database,
    'username'  => $username,
    'password'  => $password,
    'charset'   => 'utf8',
    'prefix'    => '',
    'schema'    => 'public',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();