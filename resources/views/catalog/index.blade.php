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
                    <div class="flex gap-2">
                        <input type="text" name="q" value="{{ $filters['q'] }}"
                               placeholder="Buscar anime... (o habla 🎤)"
                               class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <button type="button" id="btn-voz" title="Buscar por voz"
                                class="px-3 rounded-lg bg-indigo-600 text-white hover:brightness-125">🎤</button>
                    </div>
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

            {{-- 🌸 Anime de temporada (solo en la primera carga sin filtros) --}}
            @if(!empty($season))
                <div class="mb-10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🌸 En emisión esta temporada</h3>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                        @foreach($season as $anime)
                            <a href="{{ route('catalog.show', $anime['mal_id']) }}"
                               class="group block bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                                <div class="aspect-[2/3] overflow-hidden bg-gray-200 dark:bg-gray-700">
                                    <img src="{{ $anime['images']['jpg']['image_url'] }}"
                                         alt="{{ $anime['title'] }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <p class="p-2 text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $anime['title'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Grid de resultados --}}
            @if(count($animeList) > 0)
                <div id="anime-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @include('catalog._anime_grid', ['animeList' => $animeList])
                </div>
                {{-- Centinela de scroll infinito --}}
                <div id="load-sentinel" class="h-10"></div>
                {{-- Botón cargar más --}}
                <div class="text-center mt-8">
                    <button id="btn-load-more"
                            data-page="{{ $page }}"
                            data-has-more="{{ $hasMore ? '1' : '0' }}"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold {{ !$hasMore ? 'hidden' : '' }}">
                        ➕ Cargar más anime
                    </button>
                    @if(!$hasMore)
                        <p class="text-sm text-gray-500 dark:text-gray-400">Has visto todos los resultados.</p>
                    @endif
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    No se encontraron resultados. Intenta con otros criterios.
                </div>
            @endif

        </div>
    </div>

    {{-- Scripts --}}
    <script>
        // 🎤 Búsqueda por voz
        const btnVoz = document.getElementById('btn-voz');
        const inputQ = document.querySelector('input[name="q"]');
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (SR && btnVoz) {
            const rec = new SR();
            rec.lang = 'es-ES';
            rec.onresult = (e) => { inputQ.value = e.results[0][0].transcript; };
            rec.onstart = () => { btnVoz.textContent = '🔴'; };
            rec.onend = () => { btnVoz.textContent = '🎤'; };
            btnVoz.onclick = () => rec.start();
        } else if (btnVoz) {
            btnVoz.disabled = true;
            btnVoz.title = 'Tu navegador no soporta búsqueda por voz';
        }

                // ➕ Cargar más con ANTI-DUPLICADOS
        const btnLoad = document.getElementById('btn-load-more');
        const grid = document.getElementById('anime-grid');
        const vistos = new Set();
        if (grid) {
            grid.querySelectorAll('[data-mal-id]').forEach(el => vistos.add(el.dataset.malId));
        }

        async function cargarPagina() {
            if (!btnLoad || btnLoad.disabled || btnLoad.classList.contains('hidden')) return;
            const page = parseInt(btnLoad.dataset.page) + 1;
            btnLoad.disabled = true;
            btnLoad.textContent = 'Cargando...';

            const url = new URL(window.location.href);
            url.searchParams.set('page', page);

            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();

                // Filtra duplicados antes de insertar
                const tmp = document.createElement('div');
                tmp.innerHTML = data.html;
                let nuevos = 0;
                tmp.querySelectorAll('[data-mal-id]').forEach(el => {
                    if (!vistos.has(el.dataset.malId)) {
                        vistos.add(el.dataset.malId);
                        grid.appendChild(el);
                        nuevos++;
                    }
                });

                btnLoad.dataset.page = page;
                btnLoad.disabled = false;
                btnLoad.textContent = '➕ Cargar más anime';

                if (!data.hasMore) {
                    btnLoad.classList.add('hidden');
                } else if (nuevos === 0) {
                    cargarPagina(); // página de puros repetidos → salta a la siguiente
                }
            } catch (err) {
                btnLoad.disabled = false;
                btnLoad.textContent = '➕ Cargar más anime';
            }
        }

        if (btnLoad) btnLoad.addEventListener('click', cargarPagina);

        // ♾️ Scroll infinito
        const sentinel = document.getElementById('load-sentinel');
        if (sentinel) {
            const io = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) cargarPagina();
            }, { rootMargin: '600px' });
            io.observe(sentinel);
        }
    </script>
</x-app-layout>