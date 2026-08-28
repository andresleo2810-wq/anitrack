@foreach($animeList as $anime)
    <a href="{{ route('catalog.show', $anime['mal_id']) }}"
       data-mal-id="{{ $anime['mal_id'] }}"
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
@endforeach