<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['catalog.index', 'catalog._anime_grid'], function ($view) {
            $view->with('myIds', \App\Models\UserAnime::where('user_id', auth()->id())
                ->with('anime')->get()
                ->pluck('anime.mal_id')
                ->map(fn($m) => (int) $m)
                ->flip());
        });
    }
}