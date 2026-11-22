<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secret extends Model {
    protected $table = 'secrets';
    public $timestamps = false;
    protected $fillable = ['game_id', 'round', 'author_id', 'target_id', 'content', 'guessed_by', 'is_correct'];

    public function author() {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function target() {
        return $this->belongsTo(User::class, 'target_id');
    }
}