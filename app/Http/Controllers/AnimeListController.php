<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\UserAnime;
use Illuminate\Http\Request;

class AnimeListController extends Controller
{
    /** Mi Lista agrupada por estado */
    public function index()
    {
        $items = UserAnime::where('user_id', auth()->id())
            ->with('anime')
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('status');

        return view('mylist.index', ['items' => $items]);
    }

    /** Agregar o actualizar desde la ficha del anime */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mal_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'image_url' => 'nullable|string',
            'score_api' => 'nullable|numeric',
            'status' => 'required|in:' . implode(',', array_keys(UserAnime::STATUS_LABELS)),
            'score' => 'nullable|integer|min:1|max:10',
            'episodes_watched' => 'nullable|integer|min:0|max:10000',
        ]);

        $anime = Anime::firstOrCreate(
            ['mal_id' => $validated['mal_id']],
            [
                'title' => $validated['title'],
                'image_url' => $validated['image_url'] ?? null,
                'score' => $validated['score_api'] ?? null,
            ]
        );

        UserAnime::updateOrCreate(
            ['user_id' => auth()->id(), 'anime_id' => $anime->id],
            [
                'status' => $validated['status'],
                'score' => $validated['score'],
                'episodes_watched' => $validated['episodes_watched'] ?? 0,
            ]
        );

        return redirect()->back()->with('success', '✅ Anime guardado en tu lista');
    }

    /** Actualizar estado/puntuación desde Mi Lista */
    public function update(Request $request, UserAnime $userAnime)
    {
        abort_unless($userAnime->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(UserAnime::STATUS_LABELS)),
            'score' => 'nullable|integer|min:1|max:10',
            'episodes_watched' => 'nullable|integer|min:0|max:10000',
        ]);

        $userAnime->update($validated);

        return redirect()->back()->with('success', '✅ Lista actualizada');
    }

    /** Quitar de la lista */
    public function destroy(UserAnime $userAnime)
    {
        abort_unless($userAnime->user_id === auth()->id(), 403);

        $userAnime->delete();

        return redirect()->back()->with('success', '️ Anime eliminado de tu lista');
    }
}