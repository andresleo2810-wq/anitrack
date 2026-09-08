<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Genre;
use App\Services\JikanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncAnimeLibrary extends Command
{
    protected $signature = 'anime:sync
        {--pages=20 : Páginas de 25 anime (modo rápido: top + populares)}
        {--all : Catálogo completo vía Jikan (cuando reviva)}
        {--kitsu : Sincronizar desde Kitsu.io (DISPONIBLE AHORA)}
        {--chunk=100 : Páginas por ejecución (~2,000 anime en kitsu)}';

    protected $description = 'Descarga la biblioteca de anime a tu BD local para modo offline';

    protected const KITSU_TYPE = [
        'TV' => 'TV', 'movie' => 'Película', 'special' => 'Especial',
        'OVA' => 'OVA', 'ONA' => 'ONA', 'music' => 'Música',
    ];

    protected const KITSU_STATUS = [
        'current' => 'En emisión', 'finished' => 'Finalizado',
        'tba' => 'Próximamente', 'unreleased' => 'Próximamente',
    ];

    public function handle(JikanService $jikan): int
    {
        if ($this->option('kitsu')) {
            return $this->syncKitsu();
        }

        $probe = $jikan->rawAnimePage(1, 1);
        if (empty($probe)) {
            $this->error('⚠️ Jikan sigue caído. Usa mientras tanto: php artisan anime:sync --kitsu');
            return Command::FAILURE;
        }

        return $this->option('all') ? $this->syncAll($jikan) : $this->syncQuick($jikan);
    }

    // ============================================
    // 🟢 KITSU (funciona HOY)
    // ============================================
    protected function syncKitsu(): int
    {
        $chunk = max(10, (int) $this->option('chunk'));
        $startOffset = (int) Cache::get('sync_kitsu_offset', 0);

        $this->info("🟢 Kitsu: sincronizando desde offset {$startOffset} (trozo de {$chunk} páginas ≈ " . ($chunk * 20) . ' anime)');
        $bar = $this->output->createProgressBar($chunk);
        $bar->start();
        $saved = 0;
        $skipped = 0;

        for ($i = 0; $i < $chunk; $i++) {
            $offset = $startOffset + ($i * 20);

                        $res = Http::timeout(20)
                ->withHeaders(['Accept' => 'application/vnd.api+json'])
                ->get('https://kitsu.io/api/edge/anime', [
                    'page' => ['limit' => 20, 'offset' => $offset],
                    'include' => 'genres,mappings',
                ]);

            if (!$res->successful()) {
                $bar->finish();
                $this->newLine();
                $this->error('⚠️ Kitsu no respondió en offset ' . $offset . '. Progreso guardado. Reintenta: php artisan anime:sync --kitsu');
                return Command::FAILURE;
            }

            $data = $res->json('data') ?? [];
            if (empty($data)) {
                Cache::forget('sync_kitsu_offset');
                $bar->finish();
                $this->newLine();
                $this->info('🎉 ¡CATÁLOGO KITSU COMPLETO! Total en BD: ' . Anime::count());
                return Command::SUCCESS;
            }

            // Mapas desde included
            $included = $res->json('included') ?? [];
            $genreNames = collect($included)->where('type', 'genres')
                ->keyBy('id')->map(fn($g) => $g['attributes']['name'] ?? null);
            $malByMapping = collect($included)->where('type', 'mappings')
                ->filter(fn($m) => ($m['attributes']['externalSite'] ?? '') === 'myanimelist/anime')
                ->keyBy('id')->map(fn($m) => (int) $m['attributes']['externalId']);

            foreach ($data as $a) {
                $ref = collect($a['relationships']['mappings']['data'] ?? [])
                    ->first(fn($r) => $malByMapping->has($r['id']));

                if (!$ref) { $skipped++; continue; }

                $genres = collect($a['relationships']['genres']['data'] ?? [])
                    ->map(fn($r) => $genreNames->get($r['id']))->filter();

                $this->saveKitsu($malByMapping->get($ref['id']), $a['attributes'] ?? [], $genres);
                $saved++;
            }

            Cache::put('sync_kitsu_offset', $offset + 20, now()->addDays(60));
            $bar->advance();
            sleep(1);
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Trozo Kitsu: {$saved} guardados · {$skipped} sin mal_id · Biblioteca total: " . Anime::count());
        $this->info('➡️  Continúa: php artisan anime:sync --kitsu');

        return Command::SUCCESS;
    }

    /** Guarda anime vindo de Kitsu */
    protected function saveKitsu(int $malId, array $at, $genres): void
    {
        $anime = Anime::firstOrNew(['mal_id' => $malId]);
        $anime->title = $at['titles']['en_jp'] ?? $at['canonicalTitle'] ?? $anime->title ?? 'Sin título';
        $anime->image_url = $at['posterImage']['medium'] ?? $at['posterImage']['small'] ?? $anime->image_url;
        $anime->score = isset($at['averageRating']) ? round(((float) $at['averageRating']) / 10, 2) : $anime->score;
        $anime->episodes_total = $at['episodeCount'] ?? $anime->episodes_total;
        $anime->synopsis = $at['synopsis'] ?? $anime->synopsis;
        $anime->type = self::KITSU_TYPE[$at['subtype'] ?? ''] ?? $anime->type;
        $anime->status = self::KITSU_STATUS[$at['status'] ?? ''] ?? $anime->status;
        $anime->year = !empty($at['startDate']) ? (int) substr($at['startDate'], 0, 4) : $anime->year;
        $anime->popularity = $at['popularityRank'] ?? $anime->popularity;
        $anime->save();

        if ($genres->isNotEmpty()) {
            $ids = $genres->map(fn($n) => Genre::firstOrCreate(['name' => JikanService::GENRES_ES[$n] ?? $n])->id);
            $anime->genres()->syncWithoutDetaching($ids);
        }
    }

    // ============================================
    // 🔵 JIKAN (cuando reviva)
    // ============================================
    protected function syncQuick(JikanService $jikan): int
    {
        $pages = max(1, (int) $this->option('pages'));
        $bar = $this->output->createProgressBar($pages * 2);
        $bar->start();
        $saved = 0;

        for ($p = 1; $p <= $pages; $p++) {
            $items = $jikan->getTopAnime($p, 25);
            if (empty($items)) break;
            foreach ($items as $a) { $this->save($a); $saved++; }
            $bar->advance();
            sleep(1);
        }

        for ($p = 1; $p <= $pages; $p++) {
            $items = $jikan->searchAnime(['order' => 'popularity'], 25, $p);
            if (empty($items)) break;
            foreach ($items as $a) { $this->save($a); $saved++; }
            $bar->advance();
            sleep(1);
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Procesados {$saved} · Biblioteca total: " . Anime::count());
        return Command::SUCCESS;
    }

    protected function syncAll(JikanService $jikan): int
    {
        $chunk = max(10, (int) $this->option('chunk'));
        $start = (int) Cache::get('sync_last_page', 0) + 1;
        $end = $start + $chunk - 1;

        $this->info("📦 Jikan: páginas {$start} → {$end}");
        $bar = $this->output->createProgressBar($chunk);
        $bar->start();
        $saved = 0;

        for ($p = $start; $p <= $end; $p++) {
            $items = $jikan->rawAnimePage($p, 25);
            if (empty($items)) {
                Cache::forget('sync_last_page');
                $bar->finish();
                $this->newLine();
                $this->info('🎉 ¡CATÁLOGO COMPLETO! Total: ' . Anime::count());
                return Command::SUCCESS;
            }
            foreach ($items as $a) { $this->save($a); $saved++; }
            Cache::put('sync_last_page', $p, now()->addDays(60));
            $bar->advance();
            sleep(1);
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Trozo Jikan: {$saved} · Biblioteca total: " . Anime::count());
        return Command::SUCCESS;
    }

    /** Guarda anime vindo de Jikan (géneros traducidos a español) */
    protected function save(array $a): void
    {
        $malId = $a['mal_id'] ?? null;
        if (!$malId) return;

        $anime = Anime::firstOrNew(['mal_id' => $malId]);
        $anime->title = $a['title'] ?? $anime->title ?? 'Sin título';
        $anime->image_url = $a['images']['jpg']['image_url'] ?? $anime->image_url;
        $anime->score = $a['score'] ?? $anime->score;
        $anime->episodes_total = $a['episodes'] ?? $anime->episodes_total;
        $anime->synopsis = $a['synopsis'] ?? $anime->synopsis;
        $anime->type = $a['type'] ?? $anime->type;
        $anime->status = $a['status'] ?? $anime->status;
        $anime->season = $a['season'] ?? $anime->season;
        $anime->year = $a['year']
            ?? (isset($a['aired']['from']) && is_string($a['aired']['from']) ? (int) substr($a['aired']['from'], 0, 4) : null)
            ?? $anime->year;
        $anime->studios = is_array($a['studios'] ?? null)
            ? implode(', ', array_map(fn($s) => is_array($s) ? ($s['name'] ?? '') : (string) $s, $a['studios']))
            : $anime->studios;
        $anime->popularity = $a['popularity'] ?? $anime->popularity;
        $anime->save();

        $genres = collect($a['genres'] ?? [])
            ->map(fn($g) => is_array($g) ? ($g['name'] ?? null) : $g)->filter();
        if ($genres->isNotEmpty()) {
            $ids = $genres->map(fn($n) => Genre::firstOrCreate(['name' => JikanService::GENRES_ES[$n] ?? $n])->id);
            $anime->genres()->syncWithoutDetaching($ids);
        }
    }
}