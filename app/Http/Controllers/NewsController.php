<?php

namespace App\Http\Controllers;

use App\Services\JikanService;

class NewsController extends Controller
{
    public function __construct(protected JikanService $jikan) {}

    public function index()
    {
        return view('news.index', [
            'episodes' => $this->jikan->getWatchEpisodes(12),
            'promos' => $this->jikan->getWatchPromos(8),
            'season' => $this->jikan->getSeasonNow(12),
        ]);
    }
}