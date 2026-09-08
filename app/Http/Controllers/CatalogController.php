<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\UserAnime;
use App\Services\JikanService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected JikanService $jikan) {}

    public function index(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;

        // 🔄 Reintento manual: limpia breakers
        if ($request->boolean('retry')) {
            \Illuminate\Support\Facades\Cache::forget('jikan_down');
            \Illuminate\Support\Facades\Cache::forget('anilist_down');
        }

        $filters = [
            'q' => $request->input('q', ''),
            'genre' => $request->input('genre', ''),
            'type' => $request->input('type', ''),
            'min_score' => (float) $request->input('min_score', 0),
            'mode' => $request->input('mode', 'top'),
            'year' => (int) $request->input('year', 0),
            'status' => $request->input('status', ''),
            'season' => $request->input('season', ''),
            'letter' => $request->input('letter', ''),
            'order' => $request->input('order', ''),
            'exclude' => $request->input('exclude', ''),
        ];

        $hasFilters = $filters['q'] !== '' || $filters['genre'] !== ''
            || $filters['type'] !== '' || $filters['min_score'] > 0
            || $filters['status'] !== '' || $filters['season'] !== ''
            || $filters['letter'] !== '' || $filters['order'] !== ''
            || $filters['exclude'] !== '';

        $animeList = [];
        $hasMore = false;

        if ($hasFilters) {
            $genreEn = $filters['genre'] !== ''
                ? (array_search($filters['genre'], JikanService::GENRES_ES, true) ?: null)
                : null;

            $animeList = $this->jikan->searchAnime([
                'q' => $filters['q'] ?: null,
                'genre_en' => $genreEn,
                'genre_mal' => $genreEn ? (JikanService::GENRES_MAL[$genreEn] ?? null) : null,
                'type' => $filters['type'] ?: null,
                'min_score' => $filters['min_score'],
                'status' => $filters['status'] ?: null,
                'season' => $filters['season'] ?: null,
                'letter' => $filters['letter'] ?: null,
                'order' => $filters['order'] ?: null,
                'exclude_en' => $filters['exclude'] ?: null,
            ], $perPage, $page);

            $hasMore = $this->jikan->lastHasMore;
        } elseif ($filters['year'] > 0) {
            $animeList = $this->jikan->getByYear($filters['year']);
            $hasMore = false;
        } else {
            $animeList = match ($filters['mode']) {
                'popular' => $this->jikan->getPopular($perPage),
                'airing' => $this->jikan->getSeasonNow($perPage),
                'upcoming' => $this->jikan->getSeasonUpcoming($perPage),
                default => $this->jikan->getTopAnime($page, $perPage),
            };
            $hasMore = $filters['mode'] === 'top' ? $this->jikan->lastHasMore : false;
        }

        // 📴 Modo offline: si las APIs fallaron, busca en la BD local
        $offline = false;
        if (empty($animeList)) {
            $animeList = $this->localSearch($filters, $perPage, $page);
            $offline = !empty($animeList);
            $hasMore = count($animeList) >= $perPage;
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('catalog._anime_grid', ['animeList' => $animeList])->render(),
                'hasMore' => $hasMore,
            ]);
        }

        return view('catalog.index', [
            'animeList' => $animeList,
            'query' => $filters['q'],
            'filters' => $filters,
            'hasMore' => $hasMore,
            'page' => $page,
            'offline' => $offline,
        ]);
    }

    /**
     * 📴 Búsqueda en la BD local con filtros reales
     * Funciona con cualquier cantidad de anime sincronizado
     */
    protected function localSearch(array $filters, int $perPage, int $page): array
    {
        $query = Anime::query();

        // Búsqueda por texto
        if (!empty($filters['q'])) {
            $query->where('title', 'like', '%' . $filters['q'] . '%');
        }

        // Filtro por género
        if (!empty($filters['genre'])) {
            $query->whereHas('genres', function ($q) use ($filters) {
                $q->where('name', $filters['genre']);
            });
        }

        // Filtro por tipo
        if (!empty($filters['type'])) {
            $typeMap = ['TV' => 'TV', 'MOVIE' => 'Película', 'OVA' => 'OVA', 'ONA' => 'ONA'];
            $query->where('type', $typeMap[$filters['type']] ?? $filters['type']);
        }

        // Filtro por puntuación mínima
        if ($filters['min_score'] > 0) {
            $query->where('score', '>=', $filters['min_score']);
        }

        // Filtro por año
        if (!empty($filters['year']) && $filters['year'] > 0) {
            $query->where('year', $filters['year']);
        }

        // Filtro por estado
        if (!empty($filters['status'])) {
            $statusMap = ['airing' => 'En emisión', 'complete' => 'Finalizado', 'upcoming' => 'Próximamente'];
            $query->where('status', $statusMap[$filters['status']] ?? $filters['status']);
        }

        // Filtro por temporada
        if (!empty($filters['season'])) {
            $query->where('season', $filters['season']);
        }

        // Filtro por letra
        if (!empty($filters['letter'])) {
            $query->where('title', 'like', $filters['letter'] . '%');
        }

        // Orden
        $query->orderByDesc(match ($filters['order'] ?? '') {
            'score' => 'score',
            'popularity' => 'popularity',
            'title' => \Illuminate\Support\Facades\DB::raw('0'), // no desc para title
            'recent' => 'year',
            default => 'score',
        });

        if (($filters['order'] ?? '') === 'title') {
            $query->reorder('title', 'asc');
        }

        // Paginación
        $results = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $results->map(fn($a) => [
            'mal_id' => (int) $a->mal_id,
            'title' => $a->title,
            'images' => ['jpg' => ['image_url' => $a->image_url]],
            'score' => $a->score !== null ? (float) $a->score : null,
            'type' => $a->type ?? 'TV',
        ])->values()->all();
    }

    /** Ficha de detalle */
    public function show(int $malId)
    {
        $anime = $this->jikan->getAnimeById($malId);

        // 📴 Fallback: si la API no responde, busca en BD local
        if (!$anime) {
            $local = Anime::where('mal_id', $malId)->first();
            if ($local) {
                $anime = [
                    'mal_id' => (int) $local->mal_id,
                    'title' => $local->title,
                    'title_spanish' => null,
                    'title_english' => null,
                    'title_japanese' => null,
                    'synopsis' => $local->synopsis ?? 'Sinopsis no disponible en modo offline.',
                    'type' => $local->type ?? 'TV',
                    'episodes' => $local->episodes_total,
                    'status' => $local->status ?? 'Desconocido',
                    'score' => $local->score,
                    'popularity' => $local->popularity,
                    'genres' => $local->genres->map(fn($g) => ['name' => $g->name])->all(),
                    'images' => ['jpg' => ['image_url' => $local->image_url, 'large_image_url' => $local->image_url]],
                    'studios' => $local->studios ? collect(explode(', ', $local->studios))->map(fn($s) => ['name' => $s])->all() : [],
                    'season' => $local->season,
                    'year' => $local->year,
                    'aired' => ['from' => null, 'to' => null],
                    'trailer_url' => null,
                    'duration' => null,
                    'offline' => true,
                ];
            }
        }

        if (!$anime) {
            abort(404, 'Anime no encontrado');
        }

        $userAnime = null;
        $local = Anime::where('mal_id', $malId)->first();
        if ($local) {
            $userAnime = UserAnime::where('user_id', auth()->id())
                ->where('anime_id', $local->id)->first();
        }

        return view('catalog.show', [
            'anime' => $anime,
            'userAnime' => $userAnime,
            'similar' => ($anime['offline'] ?? false) ? [] : $this->jikan->getRecommendations($malId),
            'characters' => ($anime['offline'] ?? false) ? [] : $this->jikan->getCharacters($malId),
            'relations' => ($anime['offline'] ?? false) ? [] : $this->jikan->getRelations($malId),
            'pictures' => ($anime['offline'] ?? false) ? [] : $this->jikan->getPictures($malId),
            'themes' => ($anime['offline'] ?? false) ? ['openings' => [], 'endings' => []] : $this->jikan->getThemes($malId),
        ]);
    }
}