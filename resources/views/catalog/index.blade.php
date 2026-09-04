<x-app-layout>
    @php
        $genres_es = \App\Services\JikanService::GENRES_ES;
    @endphp

    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- HERO CATÁLOGO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="flex flex-col gap-3">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-400">Explora el universo anime</p>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Catálogo <span class="text-pink-400">✦</span>
                </h1>
                <p class="text-sm text-slate-500">Encuentra tu próxima obsesión. Busca, filtra, descubre.</p>
            </div>
        </section>

        {{-- FILTROS GLASS --}}
        <form method="GET" action="{{ route('catalog.index') }}"
              class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 backdrop-blur-xl">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                {{-- Buscador + voz --}}
                <div class="relative sm:col-span-1">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-5.2-5.2M10 17a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
                    </span>
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                           placeholder="Buscar anime..."
                           class="h-11 w-full rounded-2xl border border-[#273244] bg-[#111827]/80 pl-10 pr-10 text-sm text-slate-200 outline-none placeholder:text-slate-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <button type="button" id="btn-voz" title="Buscar por voz" aria-label="Buscar por voz"
                            class="absolute inset-y-0 right-1 flex size-9 items-center justify-center rounded-xl text-pink-400 transition hover:bg-pink-500/10">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3ZM19 10v2a7 7 0 0 1-14 0v-2M12 19v3"/></svg>
                    </button>
                </div>

                {{-- Género --}}
                <select name="genre" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Todos los géneros</option>
                    @foreach($genres_es as $es)
                        <option value="{{ $es }}" @selected($filters['genre'] === $es)>{{ $es }}</option>
                    @endforeach
                </select>

                {{-- Tipo --}}
                <select name="type" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Todos los tipos</option>
                    <option value="TV" @selected($filters['type'] === 'TV')>TV</option>
                    <option value="MOVIE" @selected($filters['type'] === 'MOVIE')>Película</option>
                    <option value="OVA" @selected($filters['type'] === 'OVA')>OVA</option>
                    <option value="ONA" @selected($filters['type'] === 'ONA')>ONA</option>
                </select>

                {{-- Puntuación mínima --}}
                <select name="min_score" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="0">Cualquier puntuación</option>
                    <option value="7" @selected($filters['min_score'] == 7)>★ 7+</option>
                    <option value="8" @selected($filters['min_score'] == 8)>★ 8+</option>
                    <option value="9" @selected($filters['min_score'] == 9)>★ 9+</option>
                </select>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-pink-500 to-violet-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-pink-500/20 transition hover:scale-[1.02] focus-visible:ring-2 focus-visible:ring-pink-400">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.2-5.2M10 17a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
                    Filtrar
                </button>
                <a href="{{ route('catalog.index') }}" class="text-sm text-slate-500 transition hover:text-slate-300">Limpiar filtros</a>
            </div>
        </form>

        {{-- 🌸 TEMPORADA ACTUAL --}}
        @if(!empty($season))
            <section>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <div class="mb-2 inline-flex rounded-full border border-pink-400/20 bg-pink-400/10 px-3 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-pink-300">
                            🌸 En emisión
                        </div>
                        <h2 class="text-lg font-bold text-white">Esta temporada</h2>
                        <p class="mt-1 text-xs text-slate-500">Los anime que están saliendo ahora mismo</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    @foreach($season as $anime)
                        <x-anime-card
                            :mal-id="$anime['mal_id']"
                            :title="$anime['title'] ?? 'Sin título'"
                            :image="$anime['images']['jpg']['image_url'] ?? null"
                            :score="$anime['score'] ?? null"
                            :type="$anime['type'] ?? null"
                            :in-list="$myIds->has((int) $anime['mal_id'])"
                        />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 🎯 RESULTADOS --}}
        @if(count($animeList) > 0)
            <section>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Resultados
                            <span class="ml-2 font-mono text-sm text-slate-500">({{ count($animeList) }}{{ $hasMore ? '+' : '' }})</span>
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $filters['q'] ? 'Búsqueda: "' . $filters['q'] . '"' : 'Explora todo el catálogo' }}
                        </p>
                    </div>
                </div>

                <div id="anime-grid" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    @foreach($animeList as $anime)
                        <div data-mal-id="{{ $anime['mal_id'] }}">
                            <x-anime-card
                                :mal-id="$anime['mal_id']"
                                :title="$anime['title'] ?? 'Sin título'"
                                :image="$anime['images']['jpg']['image_url'] ?? null"
                                :score="$anime['score'] ?? null"
                                :type="$anime['type'] ?? null"
                                :in-list="$myIds->has((int) $anime['mal_id'])"
                            />
                        </div>
                    @endforeach
                </div>

                <div id="load-sentinel" class="h-10"></div>

                <div class="mt-8 text-center">
                    <button id="btn-load-more"
                            data-page="{{ $page }}"
                            data-has-more="{{ $hasMore ? '1' : '0' }}"
                            class="{{ !$hasMore ? 'hidden' : '' }} inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-cyan-500 to-violet-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:scale-[1.02] focus-visible:ring-2 focus-visible:ring-cyan-400">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                        Cargar más anime
                    </button>
                    @if(!$hasMore)
                        <p class="text-sm text-slate-500">Has visto todos los resultados.</p>
                    @endif
                </div>
            </section>
        @else
            <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-12 text-center backdrop-blur-xl">
                <p class="text-5xl">🔍</p>
                <h3 class="mt-4 text-lg font-bold text-white">No se encontraron resultados</h3>
                <p class="mt-2 text-sm text-slate-500">Intenta con otros criterios o limpia los filtros.</p>
            </section>
        @endif
    </div>

    <script>
        // 🎤 Búsqueda por voz
        const btnVoz = document.getElementById('btn-voz');
        const inputQ = document.querySelector('input[name="q"]');
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (SR && btnVoz) {
            const rec = new SR();
            rec.lang = 'es-ES';
            rec.onresult = (e) => { inputQ.value = e.results[0][0].transcript; inputQ.form.submit(); };
            rec.onstart = () => { btnVoz.style.color = '#ef4444'; };
            rec.onend = () => { btnVoz.style.color = ''; };
            btnVoz.onclick = () => rec.start();
        }

        // ➕ Cargar más con ANTI-DUPLICADOS
        const btnLoad = document.getElementById('btn-load-more');
        const grid = document.getElementById('anime-grid');
        const vistos = new Set();
        if (grid) grid.querySelectorAll('[data-mal-id]').forEach(el => vistos.add(el.dataset.malId));

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
                btnLoad.innerHTML = '<svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg> Cargar más anime';
                btnLoad.className = btnLoad.className.replace('hidden', '').trim();

                if (!data.hasMore) {
                    btnLoad.classList.add('hidden');
                } else if (nuevos === 0) {
                    cargarPagina();
                }
            } catch (err) {
                btnLoad.disabled = false;
                btnLoad.textContent = '➕ Cargar más anime';
            }
        }

        if (btnLoad) btnLoad.addEventListener('click', cargarPagina);

        const sentinel = document.getElementById('load-sentinel');
        if (sentinel) {
            const io = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) cargarPagina();
            }, { rootMargin: '600px' });
            io.observe(sentinel);
        }
    </script>
</x-app-layout>