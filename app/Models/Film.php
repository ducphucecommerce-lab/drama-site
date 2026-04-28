<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    protected $fillable = [
        'drama_id', 'platform', 'lang', 'title', 'author',
        'cover', 'synopsis', 'status', 'views', 'chapters',
        'genres', 'tags', 'raw_data', 'detail_data', 'detail_fetched',
    ];

    protected $casts = [
        'genres'   => 'array',
        'tags'     => 'array',
        'raw_data'    => 'array',
        'detail_data' => 'array',
    ];

    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeLang($query, string $lang)
    {
        return $query->where('lang', $lang);
    }
}
