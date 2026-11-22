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
    sendResponse(['success' => false, 'message' => 'La manche en cours n’est pas terminée.'], 400);
}

$game->round = $game->round + 1;
$game->status = 'submitting';
$game->save();

sendResponse(['success' => true, 'round' => $game->round, 'status' => $game->status]);
