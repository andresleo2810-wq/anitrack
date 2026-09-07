<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\AnimeListController;
use App\Http\Controllers\AchievementController;

Route::middleware(['auth'])->group(function () {
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/anime/{malId}', [CatalogController::class, 'show'])->name('catalog.show');

    Route::get('/mylist', [AnimeListController::class, 'index'])->name('mylist.index');
    Route::post('/mylist', [AnimeListController::class, 'store'])->name('mylist.store');
    Route::put('/mylist/{userAnime}', [AnimeListController::class, 'update'])->name('mylist.update');
    Route::delete('/mylist/{userAnime}', [AnimeListController::class, 'destroy'])->name('mylist.destroy');
    Route::post('/mylist/{userAnime}/increment', [AnimeListController::class, 'increment'])->name('mylist.increment');
    Route::post('/mylist/{userAnime}/rewatch', [AnimeListController::class, 'rewatch'])->name('mylist.rewatch');
    Route::post('/mylist/voice', [AnimeListController::class, 'voiceAdd'])->name('mylist.voice');
    Route::post('/mylist/import-mal', [AnimeListController::class, 'importMal'])->name('mylist.import');
    Route::get('/trash', [AnimeListController::class, 'trash'])->name('mylist.trash');
    Route::post('/mylist/{id}/restore', [AnimeListController::class, 'restore'])->name('mylist.restore');
    Route::delete('/mylist/{id}/force', [AnimeListController::class, 'forceDelete'])->name('mylist.force');
    Route::get('/mylist-export', [AnimeListController::class, 'export'])->name('mylist.export');
    Route::post('/mylist-import-json', [AnimeListController::class, 'importJson'])->name('mylist.importJson');
    Route::get('/recap', [App\Http\Controllers\DashboardController::class, 'recap'])->name('recap');
        Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');
    });

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// 🔧 Diagnóstico temporal (borrar cuando el calendario funcione)
Route::get('/debug-schedule', function () {
    return response()->json(
        array_map('count', app(\App\Services\JikanService::class)->getSchedule())
    );
})->middleware('auth');
Route::get('/debug-avisos', function () {
    $ids = \App\Models\UserAnime::where('user_id', auth()->id())
        ->where('status', 'watching')
        ->with('anime')->get()
        ->pluck('anime.mal_id')->filter()->values()->all();

    return response()->json([
        'ids_en_viendo' => $ids,
        'ultimos_episodios' => app(\App\Services\JikanService::class)->getLatestAiredEpisodes($ids),
    ]);
})->middleware('auth');