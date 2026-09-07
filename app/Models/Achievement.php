<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['key', 'name', 'description', 'icon', 'tier', 'points'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    public function tierColor(): string
    {
        return match ($this->tier) {
            'gold' => 'text-amber-400 border-amber-400/30 bg-amber-400/10',
            'silver' => 'text-slate-300 border-slate-400/30 bg-slate-400/10',
            default => 'text-orange-400 border-orange-400/30 bg-orange-400/10',
        };
    }
}