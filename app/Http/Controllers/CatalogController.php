<?php

namespace App\Http\Controllers;

use App\Services\JikanService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected JikanService $jikan) {}

    /**
     * Catálogo principal (anime populares)
     */
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        $animeList = $query
            ? $this->jikan->searchAnime($query)
            : $this->jikan->getTopAnime();

        return view('catalog.index', [
            'animeList' => $animeList,
            'query' => $query,
        ]);
    }

    /**
     * Detalle de un anime
     */
        public function show(int $malId)
    {
        $anime = $this->jikan->getAnimeById($malId);

        if (!$anime) {
            abort(404, 'Anime no encontrado');
        }

        $userAnime = null;
        $local = \App\Models\Anime::where('mal_id', $malId)->first();
        if ($local) {
            $userAnime = \App\Models\UserAnime::where('user_id', auth()->id())
                ->where('anime_id', $local->id)->first();
        }

        return view('catalog.show', [
            'anime' => $anime,
            'userAnime' => $userAnime,
        ]);
    }
}