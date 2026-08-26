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
    });

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
