<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AnimeController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/stats', [AnimeController::class, 'stats']);
    Route::get('/mylist', [AnimeController::class, 'index']);
    Route::put('/mylist/{userAnime}', [AnimeController::class, 'update']);
    Route::post('/mylist/{userAnime}/increment', [AnimeController::class, 'increment']);
});