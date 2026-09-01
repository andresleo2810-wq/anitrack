<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($creds)) {
            return response()->json(['ok' => false, 'message' => 'Credenciales inválidas'], 422);
        }

        $user = Auth::user();

        return response()->json([
            'ok' => true,
            'token' => $user->createToken('anitrack-app')->plainTextToken,
            'user' => ['name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['ok' => true]);
    }
}