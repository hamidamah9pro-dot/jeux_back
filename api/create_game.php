<?php
require_once 'headers.php';
use App\Models\User;
use App\Models\Game;

$data = getJsonInput();
$username = cleanUsername($data['username'] ?? '');

if ($username === '') {
    sendResponse(['success' => false, 'message' => 'Le pseudo est requis.'], 400);
}

$user = User::create([
    'username' => $username,
    'avatar' => avatarFor($username),
]);

do {
    $roomCode = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 5));
} while (Game::where('room_code', $roomCode)->exists());

$game = Game::create([
    'room_code' => $roomCode,
    'status' => 'waiting',
]);

$game->users()->attach($user->id, ['is_host' => true]);

sendResponse([
    'success' => true,
    'room_code' => $game->room_code,
    'game_id' => $game->id,
    'user' => $user,
    'is_host' => true,
]);
