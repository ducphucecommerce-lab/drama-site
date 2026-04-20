<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    protected $fillable = ['user_id', 'drama_id', 'platform', 'drama_title', 'cover_url', 'episode'];

    public function user() { return $this->belongsTo(User::class); }
}
