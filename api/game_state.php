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
$thisRound = roundSecrets($game);
$submittedAuthorIds = $thisRound->pluck('author_id')->all();
$isResolved = fn($s) => !is_null($s->guessed_by);

$players = $game->users->map(fn($u) => [
    'id' => $u->id,
    'username' => $u->username,
    'avatar' => $u->avatar,
    'is_host' => (bool)$u->pivot->is_host,
    'has_submitted' => in_array($u->id, $submittedAuthorIds),
])->values();

// Secret en cours : uniquement en phase "playing", et sans l'auteur
$current = currentSecret($game);
$safeCurrentSecret = $current ? [
    'id' => $current->id,
    'content' => $current->content,
    'target' => $brief($current->target),
] : null;

$format = fn($s) => [
    'id' => $s->id,
    'round' => (int)$s->round,
    'content' => $s->content,
    'target' => $brief($s->target),
    'author' => $brief($s->author),
    'guessed_by' => $s->guessed_by,
    'guessed_author' => $brief($usersById->get($s->guessed_by)),
    'is_correct' => (bool)$s->is_correct,
];

// Secrets résolus de la manche en cours, dans l'ordre de révélation
$resolved = orderedSecrets($game)->filter($isResolved)->values()->map($format);

// Secrets résolus de toutes les manches (récap final)
$allResolved = $game->secrets->filter($isResolved)
    ->sortBy(fn($s) => sprintf('%05d-%010d', $s->round, crc32($game->room_code . '-' . $s->round . '-' . $s->id)))
    ->values()->map($format);

// Points cumulés : +1 si tu devines l'auteur d'un secret sur toi, +1 si on se trompe sur ton secret.
$scores = $game->users->map(function ($u) use ($game, $isResolved, $brief) {
    $found = $game->secrets->filter(fn($s) => $isResolved($s) && $s->target_id == $u->id && $s->is_correct)->count();
    $fooled = $game->secrets->filter(fn($s) => $isResolved($s) && $s->author_id == $u->id && !$s->is_correct)->count();
    $exposed = $game->secrets->filter(fn($s) => $isResolved($s) && $s->author_id == $u->id && $s->is_correct)->count();
    return $brief($u) + ['found' => $found, 'fooled' => $fooled, 'exposed' => $exposed, 'points' => $found + $fooled];
})->sort(fn($a, $b) => [$b['points'], $b['found']] <=> [$a['points'], $a['found']])->values();

sendResponse([
    'success' => true,
    'room_code' => $game->room_code,
    'status' => $game->status,
    'round' => (int)$game->round,
    'players' => $players,
    'secrets_submitted_count' => $thisRound->count(),
    'current_secret' => $safeCurrentSecret,
    'resolved_secrets' => $resolved,
    'all_resolved_secrets' => $allResolved,
    'scores' => $scores,
]);
