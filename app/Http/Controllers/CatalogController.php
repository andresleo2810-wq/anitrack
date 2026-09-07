<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\UserAnime;
use App\Services\JikanService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected JikanService $jikan) {}

    /** Catálogo con filtros + tabs de modo + año + modo offline */
    public function index(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;

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
                'mycollection' => $this->localCollection(),
                default => $this->jikan->getTopAnime($page, $perPage),
            };
            $hasMore = $filters['mode'] === 'top' ? $this->jikan->lastHasMore : false;
        }

        // 📴 Modo offline: si ambas APIs fallaron y no hay filtros, muestra la colección local
        $offline = false;
        if (empty($animeList) && !$hasFilters && $filters['year'] === 0) {
            $animeList = $this->localCollection();
            $offline = !empty($animeList);
            $hasMore = false;
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

    /** Colección local del usuario (formato compatible con las vistas) */
    protected function localCollection(): array
    {
        return Anime::whereIn('id', UserAnime::where('user_id', auth()->id())->pluck('anime_id'))
            ->get()
            ->map(fn($a) => [
                'mal_id' => (int) $a->mal_id,
                'title' => $a->title,
                'images' => ['jpg' => ['image_url' => $a->image_url]],
                'score' => $a->score_api !== null ? (float) $a->score_api : null,
                'type' => $a->type ?? 'TV',
            ])->values()->all();
    }

    /** Ficha de detalle */
    public function show(int $malId)
    {
        $anime = $this->jikan->getAnimeById($malId);

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
            'similar' => $this->jikan->getRecommendations($malId),
            'characters' => $this->jikan->getCharacters($malId),
            'relations' => $this->jikan->getRelations($malId),
            'pictures' => $this->jikan->getPictures($malId),
            'themes' => $this->jikan->getThemes($malId),
        ]);
    }
}