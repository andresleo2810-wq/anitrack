<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\UserAnime;
use App\Services\JikanService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected JikanService $jikan) {}

    /** Catálogo con filtros avanzados */
        public function index(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;

        $filters = [
            'q' => $request->input('q', ''),
            'genre' => $request->input('genre', ''),
            'type' => $request->input('type', ''),
            'min_score' => (float) $request->input('min_score', 0),
        ];

        $hasFilters = $filters['q'] !== '' || $filters['genre'] !== ''
            || $filters['type'] !== '' || $filters['min_score'] > 0;

        if ($hasFilters) {
            $genreEn = $filters['genre'] !== ''
                ? (array_search($filters['genre'], \App\Services\JikanService::GENRES_ES, true) ?: null)
                : null;

            $animeList = $this->jikan->searchAnime([
                'q' => $filters['q'] ?: null,
                'genre_en' => $genreEn,
                'genre_mal' => $genreEn ? (\App\Services\JikanService::GENRES_MAL[$genreEn] ?? null) : null,
                'type' => $filters['type'] ?: null,
                'min_score' => $filters['min_score'],
            ], $perPage * $page);
        } else {
            $animeList = $this->jikan->getTopAnime($page, $perPage * $page);
        }

                $hasMore = $this->jikan->lastHasMore;

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
            'season' => ($hasFilters || $page > 1) ? [] : $this->jikan->getSeasonNow(12),
        ]);
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
        ]);;
    }
}