<?php
require_once 'headers.php';
use App\Models\Game;

$data = getJsonInput();
$roomCode = strtoupper(trim($data['room_code'] ?? ''));
$userId = $data['user_id'] ?? null;

$game = Game::where('room_code', $roomCode)->first();
if (!$game) {
    sendResponse(['success' => false, 'message' => 'Salon introuvable.'], 404);
}

$host = $game->users()->wherePivot('is_host', true)->first();
if (!$host || $host->id != $userId) {
    sendResponse(['success' => false, 'message' => 'Seul l’hôte peut lancer la partie.'], 403);
}

if ($game->status !== 'waiting') {
    sendResponse(['success' => false, 'message' => 'La partie a déjà commencé.'], 400);
}

// Minimum 3 : à 2 joueurs, l'auteur d'un secret serait évident.
if ($game->users()->count() < 3) {
    sendResponse(['success' => false, 'message' => 'Il faut au moins 3 joueurs pour jouer.'], 400);
}

$game->status = 'submitting';
$game->save();

sendResponse(['success' => true, 'status' => $game->status]);
