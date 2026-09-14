<?php

namespace App\Http\Controllers;

use App\Services\AnibotService;
use Illuminate\Http\Request;

class AnibotController extends Controller
{
    public function chat(Request $request, AnibotService $bot)
    {
        $msg = trim((string) $request->input('message', ''));

        if ($msg === '') {
            return response()->json(['text' => 'Te leo... escríbeme algo 😄', 'cards' => []]);
        }

        return response()->json($bot->chat($request->user(), $msg));
    }
}