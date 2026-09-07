<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Anime;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserAnime;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    /**
     * Evalúa todos los logros del usuario y desbloquea los que cumpla.
     * Devuelve array de Achievement recién desbloqueados.
     */
    public function evaluate(User $user): array
    {
        $already = UserAchievement::where('user_id', $user->id)
            ->pluck('achievement_id')->all();

        $pending = Achievement::whereNotIn('id', $already)->get();
        if ($pending->isEmpty()) return [];

        $stats = $this->stats($user);
        $unlocked = [];

        foreach ($pending as $a) {
            if ($a->key === 'otaku_supreme') continue; // se evalúa al final
            if ($this->check($a->key, $stats)) {
                UserAchievement::create([
                    'user_id' => $user->id,
                    'achievement_id' => $a->id,
                    'unlocked_at' => now(),
                ]);
                $unlocked[] = $a;
            }
        }

        // 👑 Otaku Supremo: 15+ logros totales (incluyendo los de esta tanda)
        $total = UserAchievement::where('user_id', $user->id)->count();
        $supreme = Achievement::where('key', 'otaku_supreme')->first();
        if ($supreme && !in_array($supreme->id, $already) && $total >= 15) {
            UserAchievement::create([
                'user_id' => $user->id,
                'achievement_id' => $supreme->id,
                'unlocked_at' => now(),
            ]);
            $unlocked[] = $supreme;
        }

        return $unlocked;
    }

    /** Estadísticas agregadas del usuario */
    protected function stats(User $user): array
    {
        $items = UserAnime::where('user_id', $user->id)->get();
        $animes = Anime::whereIn('id', $items->pluck('anime_id'))->get()->keyBy('id');

        // Géneros
        $genreCount = [];
        foreach ($items as $i) {
            $genres = $animes->get($i->anime_id)?->genres ?? [];
            if (is_string($genres)) $genres = json_decode($genres, true) ?? [];
            foreach ($genres as $g) {
                $genreCount[$g] = ($genreCount[$g] ?? 0) + 1;
            }
        }

        // Racha: días distintos con actividad (created/updated), contando hacia atrás desde hoy
        $dates = $items->flatMap(fn($i) => [$i->created_at?->toDateString(), $i->updated_at?->toDateString()])
            ->filter()->unique()->values()->sort()->values()->reverse()->all();
        $streak = 0;
        $expected = now()->toDateString();
        foreach ($dates as $d) {
            if ($d === $expected) {
                $streak++;
                $expected = \Carbon\Carbon::parse($expected)->subDay()->toDateString();
            } elseif ($d === \Carbon\Carbon::parse($expected)->subDay()->toDateString() && $streak === 0) {
                // empezó ayer pero hoy aún no
                $streak++;
                $expected = \Carbon\Carbon::parse($d)->subDay()->toDateString();
            } else {
                break;
            }
        }

        return [
            'total' => $items->count(),
            'completed' => $items->where('status', 'completed')->count(),
            'dropped' => $items->where('status', 'dropped')->count(),
            'scored' => $items->filter(fn($i) => $i->score !== null)->count(),
            'perfect' => $items->filter(fn($i) => (int) $i->score === 10)->count(),
            'episodes' => (int) $items->sum('episodes_watched'),
            'genres_count' => count($genreCount),
            'genre_max' => $genreCount ? max($genreCount) : 0,
            'statuses_count' => $items->pluck('status')->unique()->count(),
            'streak' => $streak,
            'night' => $items->contains(fn($i) => $i->created_at && $i->created_at->hour >= 0 && $i->created_at->hour < 5),
            'early' => $items->contains(fn($i) => $i->created_at && $i->created_at->hour >= 5 && $i->created_at->hour < 7),
            'marathon' => $items->contains(fn($i) => $i->episodes_watched >= 12 && $i->created_at && $i->updated_at && $i->created_at->isSameDay($i->updated_at)),
        ];
    }

    /** ¿Cumple la condición del logro? */
    protected function check(string $key, array $s): bool
    {
        return match ($key) {
            'first_anime' => $s['total'] >= 1,
            'first_completed' => $s['completed'] >= 1,
            'first_score' => $s['scored'] >= 1,
            'five_genres' => $s['genres_count'] >= 5,
            'night_owl' => $s['night'],
            'early_bird' => $s['early'],
            'ten_anime' => $s['total'] >= 10,
            'streak_7' => $s['streak'] >= 7,
            'fifty_episodes' => $s['episodes'] >= 50,
            'perfect_ten' => $s['perfect'] >= 1,
            'marathon' => $s['marathon'],
            'genre_master' => $s['genre_max'] >= 5,
            'dropped_never' => $s['total'] >= 10 && $s['dropped'] === 0,
            'hundred_episodes' => $s['episodes'] >= 100,
            'streak_30' => $s['streak'] >= 30,
            'fifty_anime' => $s['total'] >= 50,
            'all_statuses' => $s['statuses_count'] >= 4,
            'critic' => $s['scored'] >= 20,
            default => false,
        };
    }
}