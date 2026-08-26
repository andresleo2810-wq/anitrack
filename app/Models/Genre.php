<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['mal_id', 'name'];

    public function animes()
    {
        return $this->belongsToMany(Anime::class);
    }
}