<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Genre;
use App\Models\UserAnime;
use App\Services\AchievementService; // 🏆 LOGROS
use App\Services\JikanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnimeListController extends Controller
{
    public function __construct(
        protected JikanService $jikan,
        protected AchievementService $achievements, // 🏆 LOGROS
    ) {}

    /** Mi Lista agrupada + sugerencia aleatoria */
    public function index()
    {
        $items = UserAnime::where('user_id', auth()->id())
            ->with('anime')
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('status');

        $suggestion = UserAnime::where('user_id', auth()->id())
            ->where('status', 'plan_to_watch')
            ->with('anime')
            ->inRandomOrder()
            ->first();

        return view('mylist.index', ['items' => $items, 'suggestion' => $suggestion]);
    }

    /** Marcar que lo volviste a ver */
    public function rewatch(UserAnime $userAnime)
    {
        abort_unless($userAnime->user_id === auth()->id(), 403);

        $userAnime->increment('rewatch_count');

        return redirect()->back()->with('success', "🔁 {$userAnime->anime->title}: visto " . ($userAnime->fresh()->rewatch_count + 1) . " veces");
    }

    /** Agregar o actualizar desde la ficha */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mal_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'image_url' => 'nullable|string',
            'score_api' => 'nullable|numeric',
            'episodes_api' => 'nullable|integer',
            'genres' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(UserAnime::STATUS_LABELS)),
            'score' => 'nullable|integer|min:1|max:10',
            'episodes_watched' => 'nullable|integer|min:0|max:10000',
            'notes' => 'nullable|string|max:1000',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
        ]);

        $anime = Anime::firstOrCreate(
            ['mal_id' => $validated['mal_id']],
            [
                'title' => $validated['title'],
                'image_url' => $validated['image_url'] ?? null,
                'score' => $validated['score_api'] ?? null,
                'episodes_total' => $validated['episodes_api'] ?? null,
            ]
        );

        if (!empty($validated['genres'])) {
            $names = json_decode($validated['genres'], true) ?? [];
            $genreIds = collect($names)->filter()
                ->map(fn($name) => Genre::firstOrCreate(['name' => $name])->id);
            $anime->genres()->syncWithoutDetaching($genreIds);
        }

        $entry = UserAnime::updateOrCreate(
            ['user_id' => auth()->id(), 'anime_id' => $anime->id],
            [
                'status' => $validated['status'],
                'score' => $validated['score'],
                'episodes_watched' => $validated['episodes_watched'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        if (!empty($validated['started_at'])) $entry->started_at = $validated['started_at'];
        if (!empty($validated['finished_at'])) $entry->finished_at = $validated['finished_at'];
        if ($entry->isDirty()) $entry->save();

        $this->applySmartDates($entry);

        $this->evaluateAndFlashAchievements(); // 🏆 LOGROS

        return redirect()->back()->with('success', '✅ Anime guardado en tu lista');
    }

    /** Actualizar estado/puntuación/notas/fechas */
    public function update(Request $request, UserAnime $userAnime)
    {
        abort_unless($userAnime->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(UserAnime::STATUS_LABELS)),
            'score' => 'nullable|integer|min:1|max:10',
            'episodes_watched' => 'nullable|integer|min:0|max:10000',
            'notes' => 'nullable|string|max:1000',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
        ]);

        $userAnime->update($validated);
        $this->applySmartDates($userAnime);

        $this->evaluateAndFlashAchievements(); // 🏆 LOGROS

        return redirect()->back()->with('success', '✅ Lista actualizada');
    }

    /** +1 episodio con auto-completar al llegar al total */
    public function increment(UserAnime $userAnime)
    {
        abort_unless($userAnime->user_id === auth()->id(), 403);

        $userAnime->increment('episodes_watched');

        $total = $userAnime->anime->episodes_total;

        if ($total && $userAnime->fresh()->episodes_watched >= $total && $userAnime->status !== 'completed') {
            $userAnime->update(['status' => 'completed', 'finished_at' => now()]);
            $this->evaluateAndFlashAchievements(); // 🏆 LOGROS
            return redirect()->back()->with('success', "🎉 ¡{$userAnime->anime->title} completado automáticamente!");
        }

        $this->evaluateAndFlashAchievements(); // 🏆 LOGROS
        return redirect()->back()->with('success', '📺 Episodio registrado');
    }

    /** Comando de voz: "X ya lo vi" → busca y agrega a Completados */
    public function voiceAdd(Request $request)
    {
        $request->validate(['title' => 'required|string|min:2|max:100']);

        $results = $this->jikan->searchAnime(['q' => $request->title]);

        if (empty($results)) {
            return response()->json(['ok' => false, 'message' => '😕 No encontré "' . $request->title . '"'], 404);
        }

        $data = $results[0];

        $local = Anime::firstOrCreate(
            ['mal_id' => $data['mal_id']],
            [
                'title' => $data['title'],
                'image_url' => $data['images']['jpg']['image_url'] ?? null,
                'score' => $data['score'] ?? null,
                'episodes_total' => $data['episodes'] ?? null,
            ]
        );

        $genreNames = collect($data['genres'] ?? [])
            ->map(fn($g) => is_array($g) ? ($g['name'] ?? null) : $g)->filter();
        $genreIds = $genreNames->map(fn($n) => Genre::firstOrCreate(['name' => $n])->id);
        $local->genres()->syncWithoutDetaching($genreIds);

        $existing = UserAnime::where('user_id', auth()->id())->where('anime_id', $local->id)->first();

        UserAnime::updateOrCreate(
            ['user_id' => auth()->id(), 'anime_id' => $local->id],
            [
                'status' => 'completed',
                'episodes_watched' => $local->episodes_total ?? ($existing->episodes_watched ?? 0),
                'finished_at' => now(),
            ]
        );

        $this->evaluateAndFlashAchievements(); // 🏆 LOGROS

        return response()->json(['ok' => true, 'message' => '✅ "' . $local->title . '" agregado a Completados']);
    }

    /** Importa historial desde MAL o AniList según elija el usuario */
    public function importMal(Request $request)
    {
        $request->validate([
            'mal_username' => 'required|string|max:50',
            'source' => 'required|in:mal,anilist',
        ]);

        $username = trim($request->mal_username);

        $result = $request->source === 'mal'
            ? $this->importFromMal($username)
            : $this->importFromAniList($username);

        $this->evaluateAndFlashAchievements(); // 🏆 LOGROS

        return $result;
    }

    /** Vía MyAnimeList (Jikan) - con reintentos */
    protected function importFromMal(string $username)
    {
        $map = [
            'watching' => 'watching', 'completed' => 'completed',
            'onhold' => 'on_hold', 'dropped' => 'dropped',
            'plantowatch' => 'plan_to_watch',
        ];

        $imported = 0; $skipped = 0; $error = null;

        foreach ($map as $jikanStatus => $localStatus) {
            for ($page = 1; $page <= 2; $page++) {
                try {
                    $res = Http::timeout(15)->retry(2, 3000)
                        ->get("https://api.jikan.moe/v4/users/{$username}/animelist", [
                            'status' => $jikanStatus, 'page' => $page, 'limit' => 300,
                        ]);

                    if (!$res->successful()) break;

                    $items = $res->json('data') ?? [];
                    if (empty($items)) break;

                    foreach ($items as $e) {
                        [$i, $s] = $this->saveImported(
                            $e['mal_id'], $e['title'] ?? 'Sin título',
                            $e['images']['jpg']['image_url'] ?? null, null,
                            $localStatus, $e['score'] ?? null, $e['episodes_watched'] ?? 0
                        );
                        $imported += $i; $skipped += $s;
                    }

                    if (!$res->json('pagination.has_next_page')) break;
                    sleep(1);
                } catch (\Exception $ex) {
                    $error = 'MAL no responde (Jikan caído). Prueba la fuente AniList.';
                    break 2;
                }
            }
        }

        $msg = $error ? '⚠️ ' . $error
            : "✅ Importados {$imported} anime · {$skipped} ya estaban en tu lista";

        return redirect()->route('mylist.index')->with('success', $msg);
    }

    /** Vía AniList (GraphQL) - estable y con episodios totales */
    protected function importFromAniList(string $username)
    {
        $query = 'query($name:String){ MediaListCollection(userName:$name,type:ANIME){ lists{ status entries{ media{ idMal title{ romaji } coverImage{ large } episodes } score(format:POINT_10_INT) progress } } } }';

        $res = Http::timeout(20)->post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => ['name' => $username],
        ]);

        if (!$res->successful() || !$res->json('data.MediaListCollection')) {
            return redirect()->route('mylist.index')
                ->with('success', '⚠️ No se pudo leer esa lista de AniList (¿username correcto y público?)');
        }

        $mapStatus = [
            'CURRENT' => 'watching', 'REPEATING' => 'watching',
            'COMPLETED' => 'completed', 'PAUSED' => 'on_hold',
            'DROPPED' => 'dropped', 'PLANNING' => 'plan_to_watch',
        ];

        $imported = 0; $skipped = 0;

        foreach ($res->json('data.MediaListCollection.lists') ?? [] as $list) {
            $localStatus = $mapStatus[$list['status'] ?? ''] ?? null;
            if (!$localStatus) continue;

            foreach ($list['entries'] ?? [] as $e) {
                $malId = $e['media']['idMal'] ?? null;
                if (!$malId) continue;

                [$i, $s] = $this->saveImported(
                    $malId,
                    $e['media']['title']['romaji'] ?? 'Sin título',
                    $e['media']['coverImage']['large'] ?? null,
                    $e['media']['episodes'] ?? null,
                    $localStatus, $e['score'] ?: null, $e['progress'] ?? 0
                );
                $imported += $i; $skipped += $s;
            }
        }

        return redirect()->route('mylist.index')
            ->with('success', "✅ Importados {$imported} anime · {$skipped} ya estaban en tu lista");
    }

    /** Guarda un anime importado sin duplicar. Devuelve [importados, saltados] */
    protected function saveImported($malId, $title, $image, $episodesTotal, $status, $score, $watched): array
    {
        $anime = Anime::firstOrCreate(
            ['mal_id' => $malId],
            [
                'title' => $title,
                'image_url' => $image,
                'episodes_total' => $episodesTotal,
            ]
        );

        if (UserAnime::where('user_id', auth()->id())->where('anime_id', $anime->id)->exists()) {
            return [0, 1];
        }

        UserAnime::create([
            'user_id' => auth()->id(),
            'anime_id' => $anime->id,
            'status' => $status,
            'score' => $score,
            'episodes_watched' => $watched,
        ]);

        return [1, 0];
    }

    /** Quitar de la lista (va a papelera, se puede restaurar) */
    public function destroy(UserAnime $userAnime)
    {
        abort_unless($userAnime->user_id === auth()->id(), 403);

        $userAnime->delete();

        return redirect()->back()->with('success', '🗑️ Anime enviado a la papelera');
    }

    /** Papelera: anime eliminados recientemente */
    public function trash()
    {
        $items = UserAnime::onlyTrashed()
            ->where('user_id', auth()->id())
            ->with('anime')
            ->orderByDesc('deleted_at')
            ->get();

        return view('mylist.trash', ['items' => $items]);
    }

    /** Restaurar desde la papelera */
    public function restore($id)
    {
        $userAnime = UserAnime::onlyTrashed()
            ->where('user_id', auth()->id())->findOrFail($id);

        $userAnime->restore();

        return redirect()->route('mylist.trash')->with('success', '♻️ Anime restaurado a tu lista');
    }

    /** Eliminar para siempre */
    public function forceDelete($id)
    {
        $userAnime = UserAnime::onlyTrashed()
            ->where('user_id', auth()->id())->findOrFail($id);

        $userAnime->forceDelete();

        return redirect()->route('mylist.trash')->with('success', '🔥 Eliminado permanentemente');
    }

    /** Completa fechas que falten según el estado */
    protected function applySmartDates(UserAnime $entry): void
    {
        $patch = [];

        if (!$entry->started_at && in_array($entry->status, ['watching', 'on_hold', 'completed'])) {
            $patch['started_at'] = now();
        }
        if (!$entry->finished_at && $entry->status === 'completed') {
            $patch['finished_at'] = now();
        }

        if ($patch) $entry->update($patch);
    }

    /** Descargar backup JSON de tu lista */
    public function export()
    {
        $items = UserAnime::where('user_id', auth()->id())
            ->with('anime')
            ->get()
            ->map(fn($i) => [
                'mal_id' => $i->anime->mal_id,
                'title' => $i->anime->title,
                'image_url' => $i->anime->image_url,
                'episodes_total' => $i->anime->episodes_total,
                'status' => $i->status,
                'score' => $i->score,
                'episodes_watched' => $i->episodes_watched,
                'notes' => $i->notes,
                'started_at' => $i->started_at?->toDateString(),
                'finished_at' => $i->finished_at?->toDateString(),
                'rewatch_count' => $i->rewatch_count,
            ]);

        $json = json_encode(
            ['app' => 'AniTrack', 'exported_at' => now()->toDateTimeString(), 'items' => $items],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        return response()->streamDownload(
            fn() => print($json),
            'anitrack-backup-' . now()->format('Ymd') . '.json'
        );
    }

    /** Restaurar lista desde un backup JSON */
    public function importJson(Request $request)
    {
        $request->validate(['backup' => 'required|file|mimes:json,txt|max:5120']);

        $data = json_decode(file_get_contents($request->file('backup')->getRealPath()), true);

        if (!isset($data['items']) || !is_array($data['items'])) {
            return redirect()->route('mylist.index')->with('success', '⚠️ El archivo no es un backup válido de AniTrack');
        }

        $imported = 0;
        $skipped = 0;

        foreach ($data['items'] as $e) {
            if (empty($e['mal_id'])) continue;

            $anime = Anime::firstOrCreate(
                ['mal_id' => $e['mal_id']],
                [
                    'title' => $e['title'] ?? 'Sin título',
                    'image_url' => $e['image_url'] ?? null,
                    'episodes_total' => $e['episodes_total'] ?? null,
                ]
            );

            if (UserAnime::where('user_id', auth()->id())->where('anime_id', $anime->id)->exists()) {
                $skipped++;
                continue;
            }

            UserAnime::create([
                'user_id' => auth()->id(),
                'anime_id' => $anime->id,
                'status' => $e['status'] ?? 'plan_to_watch',
                'score' => $e['score'] ?? null,
                'episodes_watched' => $e['episodes_watched'] ?? 0,
                'notes' => $e['notes'] ?? null,
                'started_at' => $e['started_at'] ?? null,
                'finished_at' => $e['finished_at'] ?? null,
                'rewatch_count' => $e['rewatch_count'] ?? 0,
            ]);
            $imported++;
        }

        $this->evaluateAndFlashAchievements(); // 🏆 LOGROS

        return redirect()->route('mylist.index')
            ->with('success', "💾 Backup restaurado: {$imported} anime · {$skipped} ya estaban");
    }

    // ============================================
    // 🏆 SISTEMA DE LOGROS
    // ============================================

    /**
     * Evalúa todos los logros del usuario y guarda los recién desbloqueados en sesión.
     * El layout los mostrará como popup neon en la siguiente carga.
     */
    protected function evaluateAndFlashAchievements(): void
    {
        try {
            $newlyUnlocked = $this->achievements->evaluate(auth()->user());

            if (!empty($newlyUnlocked)) {
                session()->flash('new_achievements', $newlyUnlocked);
            }
        } catch (\Exception $e) {
            // Silencioso: los logros nunca deben romper el flujo principal
            \Log::warning('Error evaluando logros: ' . $e->getMessage());
        }
    }
}