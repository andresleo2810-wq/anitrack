<?php

namespace App\Http\Controllers;

use App\Models\UserAnime;
use App\Services\JikanService;

class DashboardController extends Controller
{
    public function __construct(protected JikanService $jikan) {}

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

        // 🤖 IA de recomendaciones: anime de tus géneros favoritos que NO has visto
        $topGenres = $byGenre->keys()->take(2)->values();
        $recommendations = collect();

        if ($topGenres->isNotEmpty()) {
            $genreEn = array_search($topGenres->first(), JikanService::GENRES_ES, true) ?: null;

            if ($genreEn) {
                $recommendations = collect($this->jikan->searchAnime([
                        'genre_en' => $genreEn,
                        'min_score' => 8,
                    ]))
                    ->whereNotIn('mal_id', $items->pluck('anime.mal_id'))
                    ->take(6)
                    ->values();
            }
        }

        return view('dashboard', compact(
            'stats', 'byStatus', 'byGenre', 'topRated',
            'statusColors', 'recommendations', 'topGenres'
        ));
    }
}