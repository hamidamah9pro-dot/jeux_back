<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model {
    protected $table = 'games';
    public $timestamps = false;
    protected $fillable = ['room_code', 'status', 'round'];

    public function users() {
        return $this->belongsToMany(User::class, 'game_users', 'game_id', 'user_id')
                    ->withPivot('is_host');
    }

    public function secrets() {
        return $this->hasMany(Secret::class, 'game_id');
    }
}