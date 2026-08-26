<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SpanishSynopsisService
{
    protected string $userAgent = 'AniTrack/1.0 (proyecto educativo; contacto: andresleo2810@gmail.com)';

    public function get(string $title): ?array
    {
        if (!$title) return null;

        $key = 'wiki_es_' . md5(strtolower($title));

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $result = $this->fetch($title);

        if ($result) {
            Cache::put($key, $result, now()->addDays(30));
        }

        return $result;
    }

    protected function fetch(string $title): ?array
    {
        try {
            $search = Http::withHeaders(['User-Agent' => $this->userAgent])
                ->timeout(8)
                ->get('https://es.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $title,
                    'format' => 'json',
                    'srlimit' => 1,
                ]);

            $page = $search->successful() ? $search->json('query.search.0.title') : null;
            if (!$page) return null;

            $summary = Http::withHeaders(['User-Agent' => $this->userAgent])
                ->timeout(8)
                ->get('https://es.wikipedia.org/api/rest_v1/page/summary/' . rawurlencode($page));

            if (!$summary->successful()) return null;

            $extract = $summary->json('extract');
            if (!$extract) return null;

            return ['title' => $page, 'synopsis' => $extract];
        } catch (\Exception $e) {
            return null;
        }
    }
}