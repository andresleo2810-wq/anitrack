<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnime extends Model
{
    use HasFactory;

    protected $table = 'user_anime';

    protected $fillable = [
        'user_id', 'anime_id', 'status', 'score', 'episodes_watched', 'notes'
    ];

    public const STATUS_LABELS = [
        'watching' => 'Viendo',
        'completed' => 'Completado',
        'plan_to_watch' => 'Por ver',
        'on_hold' => 'En pausa',
        'dropped' => 'Abandonado',
    ];

    public const STATUS_COLORS = [
        'watching' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'plan_to_watch' => 'bg-gray-100 text-gray-800',
        'on_hold' => 'bg-yellow-100 text-yellow-800',
        'dropped' => 'bg-red-100 text-red-800',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}