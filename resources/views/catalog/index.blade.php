<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Catálogo de Anime') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Buscador --}}
            <form method="GET" action="{{ route('catalog.index') }}" class="mb-8">
                <input type="text" name="q" value="{{ $query }}"
                       placeholder="Buscar anime (ej: Naruto, One Piece, Attack on Titan)..."
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
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
                    No se encontraron resultados. Intenta con otro término.
                </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>