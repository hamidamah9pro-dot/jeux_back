<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $table = 'users';
    public $timestamps = false;
    protected $fillable = ['username', 'avatar'];

    public function games() {
        return $this->belongsToMany(Game::class, 'game_users', 'user_id', 'game_id')
                    ->withPivot('is_host');
    }
}