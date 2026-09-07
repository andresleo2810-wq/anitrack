<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // 🥉 BRONCE (10 pts)
            ['key' => 'first_anime', 'name' => 'Primer Paso', 'description' => 'Agrega tu primer anime a tu lista', 'icon' => '🌱', 'tier' => 'bronze', 'points' => 10],
            ['key' => 'first_completed', 'name' => 'Misión Cumplida', 'description' => 'Completa tu primer anime', 'icon' => '🎯', 'tier' => 'bronze', 'points' => 10],
            ['key' => 'first_score', 'name' => 'Crítico Novato', 'description' => 'Puntúa tu primer anime', 'icon' => '⭐', 'tier' => 'bronze', 'points' => 10],
            ['key' => 'five_genres', 'name' => 'Explorador', 'description' => 'Ten animes de 5 géneros distintos', 'icon' => '🎨', 'tier' => 'bronze', 'points' => 10],
            ['key' => 'night_owl', 'name' => 'Búho Nocturno', 'description' => 'Registra un anime después de medianoche', 'icon' => '🌙', 'tier' => 'bronze', 'points' => 10],
            ['key' => 'early_bird', 'name' => 'Madrugador', 'description' => 'Registra un anime antes de las 7am', 'icon' => '🌅', 'tier' => 'bronze', 'points' => 10],

            // 🥈 PLATA (25 pts)
            ['key' => 'ten_anime', 'name' => 'Coleccionista', 'description' => 'Ten 10 animes en tu lista', 'icon' => '📚', 'tier' => 'silver', 'points' => 25],
            ['key' => 'streak_7', 'name' => 'Racha Semanal', 'description' => 'Registra actividad 7 días seguidos', 'icon' => '🔥', 'tier' => 'silver', 'points' => 25],
            ['key' => 'fifty_episodes', 'name' => 'Medio Centenar', 'description' => 'Marca 50 episodios vistos en total', 'icon' => '📺', 'tier' => 'silver', 'points' => 25],
            ['key' => 'perfect_ten', 'name' => 'Obra Maestra', 'description' => 'Puntúa un anime con ★10', 'icon' => '💯', 'tier' => 'silver', 'points' => 25],
            ['key' => 'marathon', 'name' => 'Maratonista', 'description' => 'Marca 12+ episodios de un anime en un día', 'icon' => '🏃', 'tier' => 'silver', 'points' => 25],
            ['key' => 'genre_master', 'name' => 'Especialista', 'description' => 'Ten 5 animes de un mismo género', 'icon' => '🎭', 'tier' => 'silver', 'points' => 25],
            ['key' => 'dropped_never', 'name' => 'Comprometido', 'description' => 'Ten 10 animes sin ninguno abandonado', 'icon' => '🤝', 'tier' => 'silver', 'points' => 25],

            // 🥇 ORO (50 pts)
            ['key' => 'hundred_episodes', 'name' => 'Centenario', 'description' => 'Marca 100 episodios vistos en total', 'icon' => '💎', 'tier' => 'gold', 'points' => 50],
            ['key' => 'streak_30', 'name' => 'Racha Mensual', 'description' => 'Registra actividad 30 días seguidos', 'icon' => '📅', 'tier' => 'gold', 'points' => 50],
            ['key' => 'fifty_anime', 'name' => 'Biblioteca Andante', 'description' => 'Ten 50 animes en tu lista', 'icon' => '🏛️', 'tier' => 'gold', 'points' => 50],
            ['key' => 'all_statuses', 'name' => 'Equilibrado', 'description' => 'Ten animes en los 4 estados distintos', 'icon' => '⚖️', 'tier' => 'gold', 'points' => 50],
            ['key' => 'critic', 'name' => 'Crítico Experto', 'description' => 'Puntúa 20 animes', 'icon' => '🖋️', 'tier' => 'gold', 'points' => 50],
            ['key' => 'otaku_supreme', 'name' => 'Otaku Supremo', 'description' => 'Desbloquea 15 logros', 'icon' => '👑', 'tier' => 'gold', 'points' => 50],
        ];

        foreach ($achievements as $a) {
            Achievement::updateOrCreate(['key' => $a['key']], $a);
        }
    }
}