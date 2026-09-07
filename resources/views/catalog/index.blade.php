<x-app-layout>
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
            {{-- Filtros básicos --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-5.2-5.2M10 17a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
                    </span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Buscar anime..."
                           class="h-11 w-full rounded-2xl border border-[#273244] bg-[#111827]/80 pl-10 pr-10 text-sm text-slate-200 outline-none placeholder:text-slate-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <button type="button" id="btn-voz" title="Buscar por voz" aria-label="Buscar por voz"
                            class="absolute inset-y-0 right-1 flex size-9 items-center justify-center rounded-xl text-pink-400 transition hover:bg-pink-500/10">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3ZM19 10v2a7 7 0 0 1-14 0v-2M12 19v3"/></svg>
                    </button>
                </div>

                <select name="genre" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                    <option value="">Todos los géneros</option>
                    @foreach(\App\Services\JikanService::GENRES_ES as $es)
                        <option value="{{ $es }}" @selected($filters['genre'] === $es)>{{ $es }}</option>
                    @endforeach
                </select>

                <select name="type" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                    <option value="">Todos los tipos</option>
                    <option value="TV" @selected($filters['type'] === 'TV')>TV</option>
                    <option value="MOVIE" @selected($filters['type'] === 'MOVIE')>Película</option>
                    <option value="OVA" @selected($filters['type'] === 'OVA')>OVA</option>
                    <option value="ONA" @selected($filters['type'] === 'ONA')>ONA</option>
                </select>

                <select name="min_score" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                    <option value="0">Cualquier puntuación</option>
                    <option value="7" @selected($filters['min_score'] == 7)>★ 7+</option>
                    <option value="8" @selected($filters['min_score'] == 8)>★ 8+</option>
                    <option value="9" @selected($filters['min_score'] == 9)>★ 9+</option>
                </select>

                <select name="year" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                    <option value="">Todos los años</option>
                    @for($y = now()->year; $y >= 1990; $y--)
                        <option value="{{ $y }}" @selected($filters['year'] === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- ⚙️ Filtros avanzados (colapsables) --}}
            <details class="mt-4">
                <summary class="cursor-pointer font-mono text-xs font-bold uppercase tracking-wider text-cyan-400 hover:text-cyan-300">
                    ⚙️ Filtros avanzados
                </summary>
                <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-5">
                    <select name="status" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                        <option value="">Todos los estados</option>
                        <option value="airing" @selected($filters['status'] === 'airing')>En emisión</option>
                        <option value="complete" @selected($filters['status'] === 'complete')>Finalizado</option>
                        <option value="upcoming" @selected($filters['status'] === 'upcoming')>Próximamente</option>
                    </select>

                    <select name="season" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                        <option value="">Todas las temporadas</option>
                        <option value="winter" @selected($filters['season'] === 'winter')>❄️ Invierno</option>
                        <option value="spring" @selected($filters['season'] === 'spring')>🌸 Primavera</option>
                        <option value="summer" @selected($filters['season'] === 'summer')>☀️ Verano</option>
                        <option value="fall" @selected($filters['season'] === 'fall')>🍂 Otoño</option>
                    </select>

                    <select name="order" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                        <option value="">Orden: por defecto</option>
                        <option value="score" @selected($filters['order'] === 'score')>Mejor puntuados</option>
                        <option value="popularity" @selected($filters['order'] === 'popularity')>Más populares</option>
                        <option value="title" @selected($filters['order'] === 'title')>A → Z</option>
                        <option value="recent" @selected($filters['order'] === 'recent')>Más recientes</option>
                    </select>

                    <select name="letter" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                        <option value="">Todas las letras</option>
                        @foreach(range('A', 'Z') as $l)
                            <option value="{{ $l }}" @selected($filters['letter'] === $l)>{{ $l }}</option>
                        @endforeach
                    </select>

                    <select name="exclude" class="h-11 cursor-pointer rounded-2xl border border-[#273244] bg-[#111827]/80 px-3 text-sm text-slate-200 outline-none focus:border-cyan-400">
                        <option value="">Excluir género: ninguno</option>
                        @foreach(\App\Services\JikanService::GENRES_ES as $es)
                            <option value="{{ $es }}" @selected($filters['exclude'] === $es)>{{ $es }}</option>
                        @endforeach
                    </select>
                </div>
            </details>

            {{-- 🧭 Tabs de modo --}}
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach(['top' => '⭐ Top', 'popular' => '🔥 Popular', 'airing' => '📺 Emitiéndose', 'upcoming' => '📅 Próximamente'] as $key => $label)
                    <a href="{{ route('catalog.index', ['mode' => $key]) }}"
                       class="{{ $filters['mode'] === $key ? 'bg-gradient-to-r from-pink-500 to-violet-500 text-white shadow-lg shadow-pink-500/20' : 'border border-[#273244] text-slate-400 hover:text-white' }} rounded-full px-4 py-2 font-mono text-xs font-bold transition">
                        {{ $label }}
                    </a>
                @endforeach
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

        {{-- 📴 Aviso modo offline --}}
        @if(!empty($offline))
            <div class="flex items-start gap-3 rounded-2xl border border-amber-400/20 bg-amber-500/10 p-4 text-sm text-amber-300 backdrop-blur">
                <span class="text-lg">📴</span>
                <div>
                    <p class="font-bold">Modo offline activado</p>
                    <p class="mt-1 text-xs text-amber-200/70">MyAnimeList y AniList están caídos ahora mismo. Mostrando tu colección local — la app se reconectará sola cuando los servicios revivan.</p>
                </div>
            </div>
        @endif

        {{-- 🎯 RESULTADOS --}}
        @if(count($animeList) > 0)
            <section>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            {{ ['top' => '⭐ Top histórico', 'popular' => '🔥 Más populares', 'airing' => '📺 Emitiéndose ahora', 'upcoming' => '📅 Próximamente'][$filters['mode']] ?? 'Resultados' }}
                            <span class="ml-2 font-mono text-sm text-slate-500">({{ count($animeList) }}{{ $hasMore ? '+' : '' }})</span>
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            @if($filters['q'])
                                Búsqueda: "{{ $filters['q'] }}"
                            @elseif($filters['year'])
                                Mejores de {{ $filters['year'] }}
                            @else
                                Explora todo el catálogo
                            @endif
                        </p>
                    </div>
                </div>

                <div id="anime-grid" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    @include('catalog._anime_grid', ['animeList' => $animeList])
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