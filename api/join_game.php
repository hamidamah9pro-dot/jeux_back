<?php
require_once 'headers.php';
use App\Models\User;
use App\Models\Game;

$data = getJsonInput();
$username = cleanUsername($data['username'] ?? '');
$roomCode = strtoupper(trim($data['room_code'] ?? ''));

if ($username === '' || $roomCode === '') {
    sendResponse(['success' => false, 'message' => 'Pseudo et code de salon requis.'], 400);
}

$game = Game::where('room_code', $roomCode)->first();

if (!$game) {
    sendResponse(['success' => false, 'message' => 'Salon introuvable.'], 404);
}

if ($game->status !== 'waiting') {
    sendResponse(['success' => false, 'message' => 'La partie a déjà commencé.'], 403);
}

if ($game->users()->whereRaw('LOWER(users.username) = ?', [mb_strtolower($username)])->exists()) {
    sendResponse(['success' => false, 'message' => 'Ce pseudo est déjà pris dans ce salon.'], 409);
}

$user = User::create([
    'username' => $username,
    'avatar' => avatarFor($username),
]);

$game->users()->attach($user->id, ['is_host' => false]);

sendResponse([
    'success' => true,
    'room_code' => $game->room_code,
    'game_id' => $game->id,
    'user' => $user,
    'is_host' => false,
]);
