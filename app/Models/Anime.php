<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    use HasFactory;

    protected $table = 'anime';

    protected $fillable = [
        'mal_id', 'title', 'title_english', 'synopsis', 'type',
        'episodes', 'status', 'rating', 'score', 'scored_by',
        'rank', 'popularity', 'members', 'favorites', 'source',
        'duration', 'trailer_url', 'image_url', 'episodes_total'
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('status', 'score', 'episodes_watched', 'notes');
    }
}