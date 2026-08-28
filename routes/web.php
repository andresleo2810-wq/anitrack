<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\AnimeListController;


Route::middleware(['auth'])->group(function () {
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/anime/{malId}', [CatalogController::class, 'show'])->name('catalog.show');

    Route::get('/mylist', [AnimeListController::class, 'index'])->name('mylist.index');
    Route::post('/mylist', [AnimeListController::class, 'store'])->name('mylist.store');
    Route::put('/mylist/{userAnime}', [AnimeListController::class, 'update'])->name('mylist.update');
    Route::delete('/mylist/{userAnime}', [AnimeListController::class, 'destroy'])->name('mylist.destroy');
    Route::post('/mylist/{userAnime}/increment', [AnimeListController::class, 'increment'])->name('mylist.increment');
    Route::post('/mylist/voice', [AnimeListController::class, 'voiceAdd'])->name('mylist.voice');
    Route::post('/mylist/import-mal', [AnimeListController::class, 'importMal'])->name('mylist.import');
    Route::get('/trash', [AnimeListController::class, 'trash'])->name('mylist.trash');
    Route::post('/mylist/{id}/restore', [AnimeListController::class, 'restore'])->name('mylist.restore');
    Route::delete('/mylist/{id}/force', [AnimeListController::class, 'forceDelete'])->name('mylist.force');
        Route::post('/mylist/{userAnime}/rewatch', [AnimeListController::class, 'rewatch'])->name('mylist.rewatch');
        Route::get('/mylist-export', [AnimeListController::class, 'export'])->name('mylist.export');
    Route::post('/mylist-import-json', [AnimeListController::class, 'importJson'])->name('mylist.importJson');
    Route::get('/mylist-export', [AnimeListController::class, 'export'])->name('mylist.export');
Route::post('/mylist-import-json', [AnimeListController::class, 'importJson'])->name('mylist.importJson');   
    });

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
