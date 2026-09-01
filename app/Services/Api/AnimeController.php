<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAnime;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    public function stats()
    {
        $items = UserAnime::where('user_id', auth()->id())->with('anime')->get();
        $scored = $items->filter(fn($i) => $i->score !== null);

        return response()->json([
            'total' => $items->count(),
            'completed' => $items->where('status', 'completed')->count(),
            'watching' => $items->where('status', 'watching')->count(),
            'episodes' => $items->sum('episodes_watched'),
            'minutes' => $items->sum('episodes_watched') * 24,
            'avg_score' => $scored->isNotEmpty() ? round($scored->avg('score'), 1) : 0,
        ]);
    }

    public function index()
    {
        $items = UserAnime::where('user_id', auth()->id())
            ->with('anime')->orderByDesc('updated_at')->get();

        return response()->json($items->map(fn($i) => [
            'id' => $i->id,
            'mal_id' => $i->anime->mal_id,
            'title' => $i->anime->title,
            'image_url' => $i->anime->image_url,
            'status' => $i->status,
            'score' => $i->score,
            'episodes_watched' => $i->episodes_watched,
            'episodes_total' => $i->anime->episodes_total,
        ]));
    }

    public function increment(UserAnime $userAnime)
    {
        abort_unless($userAnime->user_id === auth()->id(), 403);

        $userAnime->increment('episodes_watched');

        if ($userAnime->anime->episodes_total
            && $userAnime->episodes_watched >= $userAnime->anime->episodes_total) {
            $userAnime->status = 'completed';
            $userAnime->save();
        }

        return response()->json([
            'ok' => true,
            'episodes' => $userAnime->episodes_watched,
            'status' => $userAnime->status,
        ]);
    }

    public function update(Request $request, UserAnime $userAnime)
    {
        abort_unless($userAnime->user_id === auth()->id(), 403);

        $data = $request->validate([
            'status' => 'sometimes|in:watching,completed,plan_to_watch,on_hold,dropped',
            'score' => 'nullable|integer|between:1,10',
            'episodes_watched' => 'sometimes|integer|min:0',
        ]);

        $userAnime->update($data);

        return response()->json(['ok' => true, 'item' => $userAnime->fresh()]);
    }
}