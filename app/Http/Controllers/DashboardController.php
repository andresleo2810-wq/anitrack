<?php

namespace App\Http\Controllers;

use App\Models\UserAnime;

class DashboardController extends Controller
{
    public function index()
    {
        $items = UserAnime::where('user_id', auth()->id())
            ->with('anime.genres')
            ->get();

        $scored = $items->filter(fn($i) => $i->score !== null);

        $stats = [
            'total' => $items->count(),
            'completed' => $items->where('status', 'completed')->count(),
            'watching' => $items->where('status', 'watching')->count(),
            'episodes' => $items->sum('episodes_watched'),
            'avg_score' => $scored->isNotEmpty() ? round($scored->avg('score'), 1) : 0,
        ];

        $byStatus = $items->groupBy('status')->map(fn($g) => $g->count());

        $byGenre = $items
            ->flatMap(fn($i) => $i->anime->genres->pluck('name'))
            ->countBy()
            ->sortDesc()
            ->take(6);

        $topRated = $scored->sortByDesc('score')->take(5);

        $statusColors = [
            'watching' => '#3b82f6',
            'completed' => '#22c55e',
            'plan_to_watch' => '#6b7280',
            'on_hold' => '#eab308',
            'dropped' => '#ef4444',
        ];

        return view('dashboard', compact('stats', 'byStatus', 'byGenre', 'topRated', 'statusColors'));
    }
}