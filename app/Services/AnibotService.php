<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\User;
use App\Models\UserAnime;

class AnibotService
{
    public function __construct(protected LocalRecommendationService $recs) {}

    /** Router de intenciones en español */
    public function chat(User $user, string $msg): array
    {
        $m = mb_strtolower(trim($msg));

        if (preg_match('/^(hola|buenas|hey|oi|holi|qué tal|que tal|buenos días|buenas noches)/u', $m)) {
            return $this->greet($user);
        }
        if (preg_match('/(como|parecid\w*|similar a|al estilo de)\s+(.{2,60})/u', $m, $mm)) {
            return $this->likeThis($mm[2]);
        }
        if (str_contains($m, 'noche') || str_contains($m, 'hoy') || str_contains($m, 'qué veo') || str_contains($m, 'que veo')) {
            return $this->tonight($user);
        }
        if (str_contains($m, 'finde') || str_contains($m, 'fin de semana') || str_contains($m, 'plan')) {
            return $this->weekend($user);
        }
        if (str_contains($m, 'joya') || str_contains($m, 'oculta') || str_contains($m, 'poco conoc')) {
            return $this->hiddenGem($user);
        }
        if (str_contains($m, 'sorprénd') || str_contains($m, 'sorprend') || str_contains($m, 'azar') || str_contains($m, 'random')) {
            return $this->surprise();
        }
        if (str_contains($m, 'resumen') || str_contains($m, 'estadíst') || str_contains($m, 'estadistic') || str_contains($m, 'cómo voy') || str_contains($m, 'como voy')) {
            return $this->summary($user);
        }
        if (str_contains($m, 'top') || str_contains($m, 'mejor') || str_contains($m, 'lo mejor')) {
            return $this->top();
        }
        foreach (JikanService::GENRES_ES as $es) {
            if (str_contains($m, mb_strtolower($es))) {
                return $this->byGenre($user, $es);
            }
        }

        return $this->help();
    }

    // ============================================
    // INTENCIONES
    // ============================================

    protected function greet(User $user): array
    {
        return ['text' => "¡Hola, {$user->name}! 🤖 Soy Anibot, tu asistente anime 100% offline.\n\nPuedo:\n🌙 Recomendarte algo para esta noche\n🎲 Sorprenderte al azar\n💎 Hallar joyas ocultas\n🎭 Recomendarte por género\n📊 Resumir tu vida otaku\n\n¿Qué se te antoja?", 'cards' => []];
    }

    protected function likeThis(string $q): array
    {
        $q = trim(preg_replace('/[¿?¡!.,]+$/', '', $q));
        $anime = Anime::where('title', 'like', "%{$q}%")->orderByDesc('score')->first();

        if (!$anime) {
            return ['text' => "😕 No tengo \"{$q}\" en mi biblioteca local. Prueba con otro título o sincroniza más anime con anime:sync.", 'cards' => []];
        }

        return ['text' => "Si te gustó {$anime->title}, su esencia vive en estos títulos:", 'cards' => $this->recs->similarTo($anime, 4)];
    }

    protected function tonight(User $user): array
    {
        $picks = Anime::whereNotIn('id', $this->myIds($user))
            ->whereNotNull('score')->where('score', '>=', 8)
            ->where(fn($q) => $q->where('type', 'Película')->orWhereBetween('episodes_total', [1, 13]))
            ->inRandomOrder()->limit(3)->get();

        if ($picks->isEmpty()) {
            return ['text' => '🌙 No encontré nada corto que no hayas visto... ¡tu lista es enorme!', 'cards' => []];
        }

        $mins = $picks->sum(fn($a) => $a->type === 'Película' ? 100 : ($a->episodes_total ?? 12) * 24);

        return ['text' => '🌙 Para esta noche: joyas cortas que no has visto. Tiempo estimado: ~' . round($mins / 60, 1) . ' h.', 'cards' => $this->cards($picks)];
    }

    protected function weekend(User $user): array
    {
        $picks = Anime::whereNotIn('id', $this->myIds($user))
            ->whereNotNull('score')->where('score', '>=', 8.2)
            ->whereBetween('episodes_total', [10, 26])
            ->orderByDesc('score')->limit(3)->get();

        if ($picks->isEmpty()) {
            return ['text' => '📅 No hallé series cortas nuevas... prueba sincronizando más anime.', 'cards' => []];
        }

        $hours = round($picks->sum(fn($a) => ($a->episodes_total ?? 12) * 24) / 60, 1);

        return ['text' => "📅 Plan de fin de semana: series completas de una sentada (~{$hours} h en total):", 'cards' => $this->cards($picks)];
    }

    protected function hiddenGem(User $user): array
    {
        $picks = Anime::whereNotIn('id', $this->myIds($user))
            ->whereNotNull('score')->where('score', '>=', 8.4)
            ->where('popularity', '>', 800)
            ->orderByDesc('score')->limit(3)->get();

        if ($picks->isEmpty()) {
            return ['text' => '💎 No hallé joyas ocultas nuevas... tu gusto es muy mainstream 😄', 'cards' => []];
        }

        return ['text' => '💎 Joyas ocultas: excelentes pero poco populares. Tesoros que casi nadie conoce:', 'cards' => $this->cards($picks)];
    }

    protected function surprise(): array
    {
        $pick = $this->recs->surprise(['min_score' => 7.5]);

        if (!$pick) {
            return ['text' => '🎲 La ruleta está vacía... sincroniza más anime para sorpresas épicas.', 'cards' => []];
        }

        return ['text' => "🎲 La ruleta dice... ¡{$pick['title']}! (★{$pick['score']}). Confía en el destino.", 'cards' => [$pick]];
    }

    protected function summary(User $user): array
    {
        $items = UserAnime::where('user_id', $user->id)->with('anime.genres')->get();

        if ($items->isEmpty()) {
            return ['text' => '📊 Tu lista está vacía aún... ¡agrega tu primer anime y te armo el resumen!', 'cards' => []];
        }

        $total = $items->count();
        $completed = $items->where('status', 'completed')->count();
        $eps = (int) $items->sum('episodes_watched');
        $avg = round($items->filter(fn($i) => $i->score)->avg('score') ?? 0, 1);

        $genreCount = [];
        foreach ($items as $i) {
            foreach ($i->anime?->genres ?? [] as $g) {
                $genreCount[$g->name] = ($genreCount[$g->name] ?? 0) + 1;
            }
        }
        arsort($genreCount);
        $topGenre = array_key_first($genreCount) ?? 'Acción';

        return ['text' => "📊 Tu resumen otaku:\n• {$total} anime en lista ({$completed} completados)\n• {$eps} episodios vistos (~" . round($eps * 24 / 60) . " h de anime en tus venas)\n• Puntuación media: ★{$avg}\n• Género favorito: {$topGenre}\n\n¿Quieres que te recomiende algo de {$topGenre}?", 'cards' => []];
    }

    protected function top(): array
    {
        $picks = Anime::whereNotNull('score')->orderByDesc('score')->limit(5)->get();

        return ['text' => '🏆 Lo mejor de mi biblioteca local (top histórico):', 'cards' => $this->cards($picks)];
    }

    protected function byGenre(User $user, string $es): array
    {
        $picks = Anime::whereNotIn('id', $this->myIds($user))
            ->whereHas('genres', fn($q) => $q->where('name', $es))
            ->whereNotNull('score')->where('score', '>=', 8)
            ->orderByDesc('score')->limit(3)->get();

        if ($picks->isEmpty()) {
            return ['text' => "🎭 No tengo {$es} sin ver que recomendarte... ¡ya lo viste todo!", 'cards' => []];
        }

        return ['text' => "🎭 Lo mejor de {$es} que aún no has visto:", 'cards' => $this->cards($picks)];
    }

    protected function help(): array
    {
        return ['text' => "🤖 Comandos que entiendo:\n• \"¿Qué veo esta noche?\"\n• \"Algo como Naruto\"\n• \"Algo de acción / romance / terror...\"\n• \"Sorpréndeme\"\n• \"Joyas ocultas\"\n• \"Plan del fin de semana\"\n• \"Mi resumen\"\n• \"Top de tu biblioteca\"", 'cards' => []];
    }

    // ============================================
    // HELPERS
    // ============================================

    protected function myIds(User $user): array
    {
        return UserAnime::where('user_id', $user->id)->pluck('anime_id')->all();
    }

    protected function cards($collection): array
    {
        return $collection->map(fn($a) => [
            'mal_id' => (int) $a->mal_id,
            'title' => $a->title,
            'image' => $a->image_url,
            'score' => $a->score !== null ? (float) $a->score : null,
        ])->values()->all();
    }
}