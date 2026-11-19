<?php
require_once 'headers.php';
use App\Models\Game;

$roomCode = strtoupper(trim($_GET['code'] ?? ''));

$game = Game::with(['users', 'secrets.target', 'secrets.author'])->where('room_code', $roomCode)->first();

if (!$game) {
    sendResponse(['success' => false, 'message' => 'Salon introuvable.'], 404);
}

$brief = fn($u) => $u ? ['id' => $u->id, 'username' => $u->username, 'avatar' => $u->avatar] : null;
$usersById = $game->users->keyBy('id');
$submittedAuthorIds = $game->secrets->pluck('author_id')->all();

// Joueurs au format attendu par le front : is_host et has_submitted à plat
$players = $game->users->map(fn($u) => [
    'id' => $u->id,
    'username' => $u->username,
    'avatar' => $u->avatar,
    'is_host' => (bool)$u->pivot->is_host,
    'has_submitted' => in_array($u->id, $submittedAuthorIds),
])->values();

// Secret en cours : uniquement en phase "playing", et sans l'auteur (anti-triche via l'onglet Réseau)
$current = currentSecret($game);
$safeCurrentSecret = $current ? [
    'id' => $current->id,
    'content' => $current->content,
    'target' => $brief($current->target),
] : null;

// Secrets déjà résolus, dans l'ordre de révélation
$resolved = orderedSecrets($game)
    ->filter(fn($s) => !is_null($s->guessed_by))
    ->values()
    ->map(fn($s) => [
        'id' => $s->id,
        'content' => $s->content,
        'target' => $brief($s->target),
        'author' => $brief($s->author),
        'guessed_by' => $s->guessed_by,
        'guessed_author' => $brief($usersById->get($s->guessed_by)),
        'is_correct' => (bool)$s->is_correct,
    ]);

sendResponse([
    'success' => true,
    'room_code' => $game->room_code,
    'status' => $game->status,
    'players' => $players,
    'secrets_submitted_count' => $game->secrets->count(),
    'current_secret' => $safeCurrentSecret,
    'resolved_secrets' => $resolved,
]);
