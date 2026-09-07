<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\UserAchievement;

class AchievementController extends Controller
{
    public function index()
    {
        $unlocked = UserAchievement::where('user_id', auth()->id())
            ->with('achievement')
            ->get()
            ->keyBy('achievement_id');

        $achievements = Achievement::orderByDesc('points')->get();

        $totalPoints = $unlocked->sum(fn($u) => $u->achievement->points ?? 0);

        return view('achievements.index', [
            'achievements' => $achievements,
            'unlocked' => $unlocked,
            'totalPoints' => $totalPoints,
        ]);
    }
}