<?php
require_once 'headers.php';
use App\Models\Game;
use App\Models\Secret;

$data = getJsonInput();
$secretId = $data['secret_id'] ?? null;
$guessedAuthorId = $data['guessed_author_id'] ?? null;
$userId = $data['user_id'] ?? null;

$secret = $secretId ? Secret::find($secretId) : null;
if (!$secret) {
    sendResponse(['success' => false, 'message' => 'Secret introuvable.'], 404);
}

$game = Game::with('secrets')->find($secret->game_id);

if ($game->status !== 'playing') {
    sendResponse(['success' => false, 'message' => 'La partie n’est pas en phase d’enquête.'], 400);
}

if ($secret->target_id != $userId) {
    sendResponse(['success' => false, 'message' => 'Ce n’est pas à toi de deviner ce secret.'], 403);
}

if (!is_null($secret->guessed_by)) {
    sendResponse(['success' => false, 'message' => 'Ce secret a déjà été résolu.'], 400);
}

$current = currentSecret($game);
if (!$current || $current->id != $secret->id) {
    sendResponse(['success' => false, 'message' => 'Ce n’est pas le secret en cours.'], 400);
}

if (!$guessedAuthorId || $guessedAuthorId == $userId || !isInGame($game, $guessedAuthorId)) {
    sendResponse(['success' => false, 'message' => 'Accusation invalide.'], 400);
}

$isCorrect = ($secret->author_id == $guessedAuthorId);
$secret->guessed_by = $guessedAuthorId;
$secret->is_correct = $isCorrect;
$secret->save();

$remaining = $game->secrets()->whereNull('guessed_by')->count();
if ($remaining === 0) {
    $game->status = 'finished';
    $game->save();
}

sendResponse([
    'success' => true,
    'is_correct' => $isCorrect,
    'real_author' => $secret->author,
    'game_status' => $game->status,
]);
