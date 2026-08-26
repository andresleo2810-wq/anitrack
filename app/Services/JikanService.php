<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JikanService
{
    protected string $baseUrl = 'https://api.jikan.moe/v4';
    protected string $anilistUrl = 'https://graphql.anilist.co';

    public function __construct(
        protected TranslationService $translator,
        protected SpanishSynopsisService $spanish,
    ) {}

    // ============ DICCIONARIOS EN ESPAÑOL (públicos para usar en controlador) ============

    public const GENRES_ES = [
        'Action' => 'Acción', 'Adventure' => 'Aventura', 'Comedy' => 'Comedia',
        'Drama' => 'Drama', 'Fantasy' => 'Fantasía', 'Horror' => 'Horror',
        'Mecha' => 'Mecha', 'Music' => 'Música', 'Mystery' => 'Misterio',
        'Psychological' => 'Psicológico', 'Romance' => 'Romance', 'Sci-Fi' => 'Ciencia ficción',
        'Slice of Life' => 'Vida cotidiana', 'Sports' => 'Deportes', 'Supernatural' => 'Sobrenatural',
        'Thriller' => 'Suspenso', 'Suspense' => 'Suspenso', 'Award Winning' => 'Premiada',
        'Ecchi' => 'Ecchi', 'Josei' => 'Josei', 'Seinen' => 'Seinen', 'Shoujo' => 'Shoujo',
        'Shounen' => 'Shounen', 'Mahou Shoujo' => 'Mahou Shoujo', 'Gourmet' => 'Gourmet',
        'Avant Garde' => 'Vanguardia', 'Boys Love' => 'Boys Love', 'Girls Love' => 'Girls Love',
    ];

    public const GENRES_MAL = [
        'Action' => 1, 'Adventure' => 2, 'Comedy' => 4, 'Drama' => 8,
        'Fantasy' => 10, 'Horror' => 14, 'Mystery' => 7, 'Romance' => 22,
        'Sci-Fi' => 24, 'Slice of Life' => 36, 'Sports' => 30,
        'Supernatural' => 37, 'Thriller' => 41,
    ];

    protected const STATUS_ES = [
        'FINISHED' => 'Finalizado', 'RELEASING' => 'En emisión',
        'NOT_YET_RELEASED' => 'Próximamente', 'CANCELLED' => 'Cancelado', 'HIATUS' => 'En pausa',
        'Finished Airing' => 'Finalizado', 'Currently Airing' => 'En emisión', 'Not yet aired' => 'Próximamente',
    ];

    protected const FORMAT_ES = [
        'TV' => 'TV', 'MOVIE' => 'Película', 'Movie' => 'Película', 'OVA' => 'OVA',
        'ONA' => 'ONA', 'SPECIAL' => 'Especial', 'Special' => 'Especial',
        'TV_SHORT' => 'Serie corta', 'MUSIC' => 'Música',
    ];

    // ============ JIKAN (MyAnimeList) ============

    protected function jikan(string $path, array $params = []): array
    {
        try {
            $response = Http::timeout(10)->retry(2, 1000)
                ->get("{$this->baseUrl}{$path}", $params);

            return $response->successful() ? ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            Log::warning('Jikan falló: ' . $e->getMessage());
            return [];
        }
    }

    // ============ ANILIST (respaldo automático) ============

    protected function anilist(string $query, array $variables = []): array
    {
        try {
            $response = Http::timeout(15)->post($this->anilistUrl, [
                'query' => $query,
                'variables' => $variables,
            ]);

            return $response->successful() ? ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            Log::warning('AniList falló: ' . $e->getMessage());
            return [];
        }
    }

    protected const FIELDS = '
        idMal id
        title { romaji english }
        coverImage { large }
        description
        episodes
        format
        status
        averageScore
        popularity
        genres
        trailer { id site }
    ';

    protected function normalize(array $m, bool $withSpanish = false): array
    {
        $romaji = $m['title']['romaji'] ?? 'Sin título';
        $es = $withSpanish ? $this->spanish->get($romaji) : null;

        return [
            'mal_id' => $m['idMal'] ?? $m['id'],
            'title' => $romaji,
            'title_spanish' => $es['title'] ?? null,
            'title_english' => $m['title']['english'] ?? null,
            'synopsis' => $es['synopsis']
                ?? $this->translator->translate(isset($m['description']) ? trim(strip_tags($m['description'])) : null),
            'type' => self::FORMAT_ES[$m['format'] ?? ''] ?? $m['format'],
            'episodes' => $m['episodes'] ?? null,
            'status' => self::STATUS_ES[$m['status'] ?? ''] ?? $m['status'],
            'score' => $m['averageScore'] ? round($m['averageScore'] / 10, 2) : null,
            'popularity' => $m['popularity'] ?? null,
            'genres' => collect($m['genres'] ?? [])
                ->map(fn($g) => self::GENRES_ES[$g] ?? $g)->all(),
            'trailer_url' => isset($m['trailer']['id']) && ($m['trailer']['site'] ?? null) === 'youtube'
                ? 'https://www.youtube.com/watch?v=' . $m['trailer']['id']
                : null,
            'images' => ['jpg' => ['image_url' => $m['coverImage']['large'] ?? null]],
        ];
    }

    protected function anilistList(string $filter, array $variables, int $limit): array
    {
        $q = "query(\$page:Int,\$perPage:Int){ Page(page:\$page,perPage:\$perPage){ media(type:ANIME{$filter}){ " . self::FIELDS . " } } }";
        $res = $this->anilist($q, array_merge(['page' => 1, 'perPage' => $limit], $variables));
        return collect($res['Page']['media'] ?? [])
            ->map(fn($m) => $this->normalize($m))->all();
    }

    // ============ BÚSQUEDA CON FILTROS (nueva versión) ============

    public function searchAnime(array $f, int $limit = 24): array
    {
        return $this->cached('search_' . md5(json_encode($f)), 1, function () use ($f, $limit) {
            // Jikan (MyAnimeList)
            $data = $this->jikan('/anime', array_filter([
                'q' => $f['q'] ?? null,
                'genres' => $f['genre_mal'] ?? null,
                'type' => !empty($f['type']) ? strtolower($f['type']) : null,
                'min_score' => ($f['min_score'] ?? 0) > 0 ? $f['min_score'] : null,
                'order_by' => ($f['min_score'] ?? 0) > 0 ? 'score' : null,
                'sort' => 'desc',
                'sfw' => 'true',
                'limit' => $limit,
            ]));
            if (!empty($data)) return $data;

            // AniList (respaldo)
            $decl = '$page:Int,$perPage:Int';
            $vars = ['page' => 1, 'perPage' => $limit];
            $filter = ', sort: ' . ((($f['min_score'] ?? 0) > 0) ? 'SCORE_DESC' : 'POPULARITY_DESC');

            if (!empty($f['q'])) {
                $decl .= ',$search:String';
                $filter .= ', search: $search';
                $vars['search'] = $f['q'];
            }
            if (!empty($f['genre_en'])) {
                $decl .= ',$genre:String';
                $filter .= ', genre: $genre';
                $vars['genre'] = $f['genre_en'];
            }
            if (!empty($f['type'])) {
                $decl .= ',$format:MediaFormat';
                $filter .= ', format: $format';
                $vars['format'] = $f['type'];
            }
            if (($f['min_score'] ?? 0) > 0) {
                $decl .= ',$minScore:Int';
                $filter .= ', averageScore_greater: $minScore';
                $vars['minScore'] = (int) ($f['min_score'] * 10);
            }

            $q = "query({$decl}){ Page(page:\$page,perPage:\$perPage){ media(type:ANIME{$filter}){ " . self::FIELDS . " } } }";
            $res = $this->anilist($q, $vars);

            return collect($res['Page']['media'] ?? [])
                ->map(fn($m) => $this->normalize($m))->all();
        });
    }

    public function getTopAnime(int $page = 1, int $limit = 24): array
    {
        return $this->cached("top_{$page}", 6, function () use ($page, $limit) {
            $data = $this->jikan('/top/anime', ['page' => $page, 'limit' => $limit]);
            return !empty($data) ? $data
                : $this->anilistList(', sort: SCORE_DESC', [], $limit);
        });
    }

    public function getAnimeById(int $malId): ?array
    {
        $data = $this->jikan("/anime/{$malId}/full");

        if (!empty($data) && isset($data['mal_id'])) {
            $es = $this->spanish->get($data['title'] ?? '');

            $data['title_spanish'] = $es['title'] ?? null;
            $data['synopsis'] = $es['synopsis'] ?? $this->translator->translate($data['synopsis'] ?? null);
            $data['genres'] = collect($data['genres'] ?? [])
                ->map(fn($g) => ['name' => self::GENRES_ES[$g['name'] ?? ''] ?? ($g['name'] ?? '')])->all();
            $data['status'] = self::STATUS_ES[$data['status'] ?? ''] ?? $data['status'];
            $data['type'] = self::FORMAT_ES[$data['type'] ?? ''] ?? $data['type'];
            return $data;
        }

        $q = "query(\$idMal:Int){ Media(idMal:\$idMal, type:ANIME){ " . self::FIELDS . " } }";
        $res = $this->anilist($q, ['idMal' => $malId]);
        return isset($res['Media']) ? $this->normalize($res['Media'], true) : null;
    }

    public function getSeasonNow(int $limit = 24): array
    {
        return $this->cached('season_now', 2, function () use ($limit) {
            $data = $this->jikan('/seasons/now', ['limit' => $limit]);
            if (!empty($data)) return $data;

            $month = now()->month;
            $season = match (true) {
                $month <= 3 => 'WINTER',
                $month <= 6 => 'SPRING',
                $month <= 9 => 'SUMMER',
                default => 'FALL',
            };
            return $this->anilistList(', season: $season, seasonYear: $year', [
                'season' => $season, 'year' => now()->year,
            ], $limit);
        });
    }

    protected function cached(string $key, int $hours, callable $fetch): array
    {
        return Cache::remember($key, now()->addHours($hours), function () use ($key, $fetch) {
            $data = $fetch();
            if (empty($data)) Cache::forget($key);
            return $data;
        });
    }
}