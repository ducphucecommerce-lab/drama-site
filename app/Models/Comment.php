<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['user_id', 'drama_id', 'platform', 'episode', 'content', 'likes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
