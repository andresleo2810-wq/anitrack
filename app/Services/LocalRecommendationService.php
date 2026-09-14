<?php

namespace App\Services;

use App\Models\Anime;
use Illuminate\Support\Facades\Cache;

class LocalRecommendationService
{
    /**
     * Anime similares desde la BD local:
     * géneros compartidos (Jaccard) + mismo tipo + misma época + score alto.
     * Resultado cacheado 24h por anime.
     */
    public function similarTo(Anime $anime, int $limit = 6): array
    {
        return Cache::remember(
            'local_sim_' . $anime->mal_id,
            now()->addHours(24),
            fn() => $this->compute($anime, $limit)
        );
    }

    protected function compute(Anime $anime, int $limit): array
    {
        $myGenres = $anime->genres->pluck('name')->all();
        $genreIds = $anime->genres->pluck('id');

        if ($genreIds->isEmpty()) return [];

        // Solo candidatos que compartan al menos 1 género (rápido incluso con 20k)
        $candidates = Anime::with('genres')
            ->where('id', '!=', $anime->id)
            ->whereNotNull('score')
            ->whereHas('genres', fn($q) => $q->whereIn('genres.id', $genreIds))
            ->limit(400)
            ->get();

        return $candidates->map(function ($c) use ($anime, $myGenres) {
            $theirGenres = $c->genres->pluck('name')->all();

            $inter = count(array_intersect($myGenres, $theirGenres));
            $union = count(array_unique(array_merge($myGenres, $theirGenres)));
            $jaccard = $union > 0 ? $inter / $union : 0;

            $score = $jaccard * 100;
            if ($c->type && $c->type === $anime->type) $score += 10;
            if ($anime->year && $c->year && abs($c->year - $anime->year) <= 5) $score += 8;
            $score += ((float) $c->score) * 1.5;

            return [
                'mal_id' => (int) $c->mal_id,
                'title' => $c->title,
                'image' => $c->image_url,
                'score' => $c->score !== null ? (float) $c->score : null,
                'match' => round($jaccard * 100),
            ];
        })
        ->sortByDesc('score')
        ->take($limit)
        ->values()
        ->all();
    }

    /** 🎲 Sorpréndeme: azar con criterio (filtros opcionales) */
    public function surprise(array $filters = []): ?array
    {
        $query = Anime::whereNotNull('score')->where('score', '>=', $filters['min_score'] ?? 7.5);

        if (!empty($filters['type'])) $query->where('type', $filters['type']);
        if (!empty($filters['genre'])) {
            $query->whereHas('genres', fn($q) => $q->where('name', $filters['genre']));
        }
        if (!empty($filters['max_eps'])) {
            $query->whereNotNull('episodes_total')->where('episodes_total', '<=', $filters['max_eps']);
        }

        $anime = $query->inRandomOrder()->first();

        return $anime ? [
            'mal_id' => (int) $anime->mal_id,
            'title' => $anime->title,
            'image' => $anime->image_url,
            'score' => (float) $anime->score,
        ] : null;
    }
}