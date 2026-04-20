<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    protected $table = 'watch_history';

    protected $fillable = ['user_id', 'drama_id', 'platform', 'drama_title', 'cover_url', 'episode'];

    public function user() { return $this->belongsTo(User::class); }
}