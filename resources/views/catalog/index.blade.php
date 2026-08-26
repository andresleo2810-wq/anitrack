<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Catálogo de Anime') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filtros avanzados --}}
            <form method="GET" action="{{ route('catalog.index') }}"
                  class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                           placeholder="Buscar anime..."
                           class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <select name="genre" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Todos los géneros</option>
                        @foreach(\App\Services\JikanService::GENRES_ES as $es)
                            <option value="{{ $es }}" @selected($filters['genre'] === $es)>{{ $es }}</option>
                        @endforeach
                    </select>
                    <select name="type" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Todos los tipos</option>
                        <option value="TV" @selected($filters['type'] === 'TV')>TV</option>
                        <option value="MOVIE" @selected($filters['type'] === 'MOVIE')>Película</option>
                        <option value="OVA" @selected($filters['type'] === 'OVA')>OVA</option>
                        <option value="ONA" @selected($filters['type'] === 'ONA')>ONA</option>
                    </select>
                    <select name="min_score" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="0">Cualquier puntuación</option>
                        <option value="7" @selected($filters['min_score'] == 7)>7+</option>
                        <option value="8" @selected($filters['min_score'] == 8)>8+</option>
                        <option value="9" @selected($filters['min_score'] == 9)>9+</option>
                    </select>
                </div>
                <div class="mt-3 flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                        🔍 Filtrar
                    </button>
                    <a href="{{ route('catalog.index') }}"
                       class="px-4 py-2 text-gray-500 dark:text-gray-400 hover:underline">Limpiar</a>
                </div>
            </form>

            {{-- Grid de anime --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @forelse($animeList as $anime)
                <a href="{{ route('catalog.show', $anime['mal_id']) }}"
                   class="group block bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                    <div class="aspect-[2/3] overflow-hidden bg-gray-200 dark:bg-gray-700">
                        <img src="{{ $anime['images']['jpg']['image_url'] }}"
                             alt="{{ $anime['title'] }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <div class="p-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                            {{ $anime['title'] }}
                        </h3>
                        <div class="mt-1 flex items-center justify-between text-xs">
                            <span class="text-yellow-500 font-semibold">★ {{ $anime['score'] ?? 'N/A' }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ $anime['type'] ?? 'TV' }}</span>
                        </div>
                    </div>
                </a>
                @empty
                <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                    No se encontraron resultados. Intenta con otros criterios.
                </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>