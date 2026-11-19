<?php
require_once 'headers.php';
use App\Models\Game;

$roomCode = strtoupper(trim($_GET['code'] ?? ''));

$game = Game::with(['users', 'secrets.target', 'secrets.author'])->where('room_code', $roomCode)->first();

if (!$game) {
    sendResponse(['success' => false, 'message' => 'Salon introuvable.'], 404);
}

// Secret actuellement en cours de devinette (le premier non résolu)
$currentSecret = $game->secrets->whereNull('guessed_by')->first();

// On masque l'auteur du secret en cours pour éviter la triche dans l'inspecteur réseau !
$safeCurrentSecret = null;
if ($currentSecret) {
    $safeCurrentSecret = [
        'id' => $currentSecret->id,
        'content' => $currentSecret->content,
        'target' => $currentSecret->target
    ];
}

// Historique des secrets déjà devinés pour les scores / résultats
$resolvedSecrets = $game->secrets->whereNotNull('guessed_by')->values()->map(function ($s) {
    return [
        'id' => $s->id,
        'content' => $s->content,
        'target' => $s->target,
        'author' => $s->author,
        'guessed_by' => $s->guessed_by,
        'is_correct' => (bool)$s->is_correct
    ];
});

sendResponse([
    'success' => true,
    'room_code' => $game->room_code,
    'status' => $game->status,
    'players' => $game->users,
    'secrets_submitted_count' => $game->secrets->count(),
    'current_secret' => $safeCurrentSecret,
    'resolved_secrets' => $resolvedSecrets
]);