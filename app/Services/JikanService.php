<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JikanService
{
    protected string $baseUrl = 'https://api.jikan.moe/v4';
    protected string $anilistUrl = 'https://graphql.anilist.co';

    /** ¿Hay más páginas después de la última consulta? (público para el controller) */
    public bool $lastHasMore = false;

    public function __construct(
        protected TranslationService $translator,
        protected SpanishSynopsisService $spanish,
    ) {}

    // ============ DICCIONARIOS EN ESPAÑOL ============

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

    // ============ JIKAN (con circuit breaker) ============

    protected function jikan(string $path, array $params = []): array
    {
        if (Cache::get('jikan_down')) return [];

        try {
            $response = Http::timeout(4)->retry(1, 300)
                ->get("{$this->baseUrl}{$path}", $params);

            if (!$response->successful()) {
                Cache::put('jikan_down', true, now()->addMinutes(10));
                return [];
            }

            Cache::forget('jikan_down');
            return $response->json('data') ?? [];
        } catch (\Exception $e) {
            Cache::put('jikan_down', true, now()->addMinutes(10));
            Log::warning('Jikan falló: ' . $e->getMessage());
            return [];
        }
    }

    // ============ ANILIST (con reintentos para 429) ============

    protected function anilist(string $query, array $variables = []): array
    {
        try {
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $response = Http::timeout(15)->post($this->anilistUrl, [
                    'query' => $query,
                    'variables' => $variables,
                ]);

                if ($response->status() === 429) {
                    sleep(2);
                    continue;
                }

                return $response->successful() ? ($response->json('data') ?? []) : [];
            }
            return [];
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

    // ============ LISTAS ANILIST CON PAGINACIÓN ============

    protected function anilistList(string $filter, array $variables, int $limit, int $page = 1): array
    {
        $q = "query(\$page:Int,\$perPage:Int){ Page(page:\$page,perPage:\$perPage){ pageInfo{ hasNextPage } media(type:ANIME{$filter}){ " . self::FIELDS . " } } }";
        $res = $this->anilist($q, array_merge(['page' => $page, 'perPage' => $limit], $variables));
        $media = $res['Page']['media'] ?? [];
        $this->lastHasMore = $res['Page']['pageInfo']['hasNextPage'] ?? (count($media) >= $limit);
        return collect($media)->map(fn($m) => $this->normalize($m))->all();
    }

    // ============ BÚSQUEDA CON FILTROS (sin caché en paginación) ============

    public function searchAnime(array $f, int $limit = 24, int $page = 1): array
    {
        // Jikan primero
        $data = $this->jikan('/anime', array_filter([
            'q' => $f['q'] ?? null,
            'genres' => $f['genre_mal'] ?? null,
            'type' => !empty($f['type']) ? strtolower($f['type']) : null,
            'min_score' => ($f['min_score'] ?? 0) > 0 ? $f['min_score'] : null,
            'order_by' => ($f['min_score'] ?? 0) > 0 ? 'score' : null,
            'sort' => 'desc',
            'sfw' => 'true',
            'page' => $page,
            'limit' => $limit,
        ]));

        if (!empty($data)) return $data;

        // AniList como respaldo con pageInfo.hasNextPage
        $decl = '$page:Int,$perPage:Int';
        $vars = ['page' => $page, 'perPage' => $limit];
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

        $q = "query({$decl}){ Page(page:\$page,perPage:\$perPage){ pageInfo{ hasNextPage } media(type:ANIME{$filter}){ " . self::FIELDS . " } } }";
        $res = $this->anilist($q, $vars);

        $media = $res['Page']['media'] ?? [];
        $this->lastHasMore = $res['Page']['pageInfo']['hasNextPage'] ?? (count($media) >= $limit);

        return collect($media)->map(fn($m) => $this->normalize($m))->all();
    }

    public function getTopAnime(int $page = 1, int $limit = 24): array
    {
        return $this->cached("top_{$page}", 1, function () use ($page, $limit) {
            $data = $this->jikan('/top/anime', ['page' => $page, 'limit' => $limit]);
            if (!empty($data)) {
                $this->lastHasMore = count($data) >= $limit;
                return $data;
            }
            return $this->anilistList(', sort: SCORE_DESC', [], $limit, $page);
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

    /** Anime similares (Jikan → AniList fallback) */
    public function getRecommendations(int $malId, int $limit = 6): array
    {
        return $this->cached("recs_{$malId}", 24, function () use ($malId, $limit) {
            $data = $this->jikan("/anime/{$malId}/recommendations");

            if (!empty($data)) {
                return collect($data)->take($limit)->map(fn($r) => [
                    'mal_id' => $r['entry']['mal_id'] ?? null,
                    'title' => $r['entry']['title'] ?? null,
                    'image' => $r['entry']['images']['jpg']['image_url'] ?? null,
                ])->filter(fn($r) => $r['mal_id'])->values()->all();
            }

            $q = 'query($idMal:Int){ Media(idMal:$idMal,type:ANIME){ recommendations(sort:RATING_DESC,perPage:6){ nodes{ mediaRecommendation{ idMal title{ romaji } coverImage{ large } } } } } }';
            $res = $this->anilist($q, ['idMal' => $malId]);

            return collect($res['Media']['recommendations']['nodes'] ?? [])
                ->map(fn($n) => [
                    'mal_id' => $n['mediaRecommendation']['idMal'] ?? null,
                    'title' => $n['mediaRecommendation']['title']['romaji'] ?? null,
                    'image' => $n['mediaRecommendation']['coverImage']['large'] ?? null,
                ])->filter(fn($r) => $r['mal_id'])->take($limit)->values()->all();
        });
    }
}