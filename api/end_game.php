<?php
require_once 'headers.php';
use App\Models\Game;

$data = getJsonInput();
$game = Game::where('room_code', strtoupper(trim($data['room_code'] ?? '')))->first();
if (!$game) {
    sendResponse(['success' => false, 'message' => 'Salon introuvable.'], 404);
}

requireHost($game, $data['user_id'] ?? null);

if ($game->status !== 'round_over') {
    sendResponse(['success' => false, 'message' => 'Termine d’abord la manche en cours.'], 400);
}

$game->status = 'finished';
$game->save();

sendResponse(['success' => true, 'status' => $game->status]);
