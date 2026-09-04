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
            'minutes' => $items->sum('episodes_watched') * 24,
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

        // 🤖 IA de recomendaciones
        $topGenres = $byGenre->keys()->take(2)->values();
        $recommendations = collect();

               if ($topGenres->isNotEmpty()) {
            $genreEn = array_search($topGenres->first(), JikanService::GENRES_ES, true) ?: null;

            if ($genreEn) {
                $recommendations = \Illuminate\Support\Facades\Cache::remember(
                    'recs_dash_' . $genreEn,
                    now()->addHours(6),
                    fn() => collect($this->jikan->searchAnime([
                            'genre_en' => $genreEn,
                            'min_score' => 8,
                        ]))
                        ->whereNotIn('mal_id', $items->pluck('anime.mal_id'))
                        ->take(6)
                        ->values()
                );
            }
        }

        $watching = UserAnime::where('user_id', auth()->id())
            ->where('status', 'watching')
            ->with('anime')
            ->orderByDesc('updated_at')
            ->take(4)
            ->get();

        // 🔔 Avisos de episodios nuevos
              // 🔔 Avisos de episodios nuevos
        $avisos = collect();
        $viendoTodo = UserAnime::where('user_id', auth()->id())
            ->where('status', 'watching')->with('anime')->get();

        $schedule = $this->jikan->getSchedule();
        $idsSemana = collect($schedule)->flatten(1)->pluck('mal_id')->filter()->unique();

        if ($viendoTodo->isNotEmpty()) {
            $ids = $viendoTodo->pluck('anime.mal_id')->filter()->values()->all();
            $latest = $this->jikan->getLatestAiredEpisodes($ids);

            foreach ($viendoTodo as $w) {
                $malId = $w->anime->mal_id;
                $ep = $latest[$malId] ?? null;

                if ($ep && $ep > $w->episodes_watched) {
                    $avisos->push([
                        'title' => $w->anime->title,
                        'image' => $w->anime->image_url,
                        'texto' => "¡el episodio {$ep} ya salió!",
                        'mal_id' => $malId,
                    ]);
                } elseif ($idsSemana->contains($malId)) {
                    $avisos->push([
                        'title' => $w->anime->title,
                        'image' => $w->anime->image_url,
                        'texto' => '¡tiene episodio nuevo esta semana!',
                        'mal_id' => $malId,
                    ]);
                }
            }
        }

        return view('dashboard', compact(
            'stats', 'byStatus', 'byGenre', 'topRated',
            'statusColors', 'recommendations', 'topGenres',
            'watching', 'schedule', 'avisos'
        ));
    }
        public function recap()
    {
        $year = now()->year;

        $items = UserAnime::where('user_id', auth()->id())
            ->whereYear('created_at', $year)
            ->with('anime.genres')
            ->get();

        $scored = $items->filter(fn($i) => $i->score !== null);
        $eps = $items->sum('episodes_watched');
        $top = $items->sortByDesc('score')->first();
        $genero = $items->flatMap(fn($i) => $i->anime->genres->pluck('name'))
            ->countBy()->sortDesc()->keys()->first();

        $porMes = $items->groupBy(fn($i) => $i->created_at->format('n'));
        $mesNum = $porMes->sortByDesc(fn($g) => $g->count())->keys()->first();
        $mes = $mesNum
            ? ucfirst(\Carbon\Carbon::create()->month((int) $mesNum)->locale('es')->isoFormat('MMMM'))
            : '—';

        return view('recap', [
            'year' => $year,
            'items' => $items,
            'eps' => $eps,
            'horas' => round($eps * 24 / 60),
            'top' => $top,
            'genero' => $genero,
            'mes' => $mes,
            'mesCount' => $mesNum ? $porMes[$mesNum]->count() : 0,
            'completados' => $items->where('status', 'completed')->count(),
            'avg' => $scored->isNotEmpty() ? round($scored->avg('score'), 1) : 0,
        ]);
    }
}