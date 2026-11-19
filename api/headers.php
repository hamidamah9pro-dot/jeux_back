<?php
// Mettre à false en production pour ne plus exposer le détail des erreurs.
const APP_DEBUG = true;

ini_set('display_errors', '0');
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Toute erreur non gérée renvoie du JSON (jamais de HTML) pour que le front puisse l'afficher.
set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur. Vérifie la base de données (database.sql importé ?).',
        'debug'   => APP_DEBUG ? $e->getMessage() : null,
    ]);
});

require_once __DIR__ . '/../config/database.php';

use App\Models\Game;

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function cleanUsername($value): string {
    return mb_substr(trim((string)$value), 0, 20);
}

function avatarFor(string $username): string {
    return "https://api.dicebear.com/7.x/avataaars/svg?seed=" . urlencode($username . rand(1, 999));
}

/**
 * Ordre de révélation : mélangé mais stable d'un appel à l'autre (même ordre pour tous les joueurs),
 * et indépendant de l'ordre dans lequel les joueurs ont soumis leur secret.
 */
function orderedSecrets(Game $game) {
    return $game->secrets
        ->sortBy(fn($s) => crc32($game->room_code . '-' . $s->id))
        ->values();
}

function currentSecret(Game $game) {
    if ($game->status !== 'playing') {
        return null;
    }
    return orderedSecrets($game)->first(fn($s) => is_null($s->guessed_by));
}

function isInGame(Game $game, $userId): bool {
    return $userId && $game->users()->where('users.id', $userId)->exists();
}
