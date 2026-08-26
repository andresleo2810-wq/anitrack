<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Services\JikanService;
use Illuminate\Console\Command;

class AttachGenres extends Command
{
    protected $signature = 'anitrack:attach-genres';
    protected $description = 'Adjunta géneros a todos los anime de la BD';

    public function handle(JikanService $jikan)
    {
        $animes = Anime::all();
        $this->info("Actualizando géneros de {$animes->count()} anime...");

        foreach ($animes as $anime) {
            $data = $jikan->getAnimeById($anime->mal_id);
            
            if (!empty($data['genres'])) {
                $names = collect($data['genres'])->map(
                    fn($g) => is_array($g) ? ($g['name'] ?? null) : $g
                )->filter()->values();

                $genreIds = $names->map(
                    fn($name) => \App\Models\Genre::firstOrCreate(['name' => $name])->id
                );

                $anime->genres()->sync($genreIds);
                $this->info("✅ {$anime->title}: " . $names->implode(', '));
            } else {
                $this->warn("⚠️ {$anime->title}: sin géneros");
            }
        }

        $this->info('¡Listo!');
    }
}