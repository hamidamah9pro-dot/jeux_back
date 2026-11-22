<?php
require_once 'headers.php';
use App\Models\Game;
use App\Models\Secret;

$data = getJsonInput();
$roomCode = strtoupper(trim($data['room_code'] ?? ''));
$authorId = $data['author_id'] ?? null;
$targetId = $data['target_id'] ?? null;
$content = mb_substr(trim($data['content'] ?? ''), 0, 280);

if ($roomCode === '' || !$authorId || !$targetId || $content === '') {
    sendResponse(['success' => false, 'message' => 'Tous les champs sont obligatoires.'], 400);
}

if ($authorId == $targetId) {
    sendResponse(['success' => false, 'message' => 'Tu ne peux pas écrire un secret sur toi-même.'], 400);
}

$game = Game::where('room_code', $roomCode)->first();
if (!$game || $game->status !== 'submitting') {
    sendResponse(['success' => false, 'message' => 'Phase de soumission fermée ou salon invalide.'], 400);
}

if (!isInGame($game, $authorId) || !isInGame($game, $targetId)) {
    sendResponse(['success' => false, 'message' => 'Joueur introuvable dans ce salon.'], 403);
}

Secret::updateOrCreate(
    ['game_id' => $game->id, 'round' => $game->round, 'author_id' => $authorId],
    ['target_id' => $targetId, 'content' => $content]
);

// Dès que tout le monde a soumis, on passe en phase d'enquête
$totalPlayers = $game->users()->count();
$totalSecrets = $game->secrets()->where('round', $game->round)->count();

if ($totalSecrets >= $totalPlayers) {
    $game->status = 'playing';
    $game->save();
}

sendResponse([
    'success' => true,
    'submitted_count' => $totalSecrets,
    'total_players' => $totalPlayers,
    'game_status' => $game->status,
]);
