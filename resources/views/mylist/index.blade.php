<x-app-layout>
    @php
        $statusHex = [
            'watching' => '#22d3ee',
            'completed' => '#34d399',
            'plan_to_watch' => '#a78bfa',
            'on_hold' => '#fbbf24',
            'dropped' => '#ec4899',
        ];
    @endphp

    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- HERO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="flex flex-col gap-3">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-400">Tu colección personal</p>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Mi Lista <span class="text-pink-400">✦</span>
                </h1>
                <p class="text-sm text-slate-500">Gestiona tu progreso, puntuaciones y rewatches.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('mylist.export') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02]">
                    ⬇️ Backup
                </a>
                <a href="{{ route('mylist.trash') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-400 transition hover:border-red-400/40 hover:text-red-400">
                    🗑️ Papelera
                </a>
            </div>
        </section>

        {{-- Flash success --}}
        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 p-4 text-sm text-emerald-300 backdrop-blur">
                <span class="text-lg">✅</span> {{ session('success') }}
            </div>
        @endif

        {{-- 🎲 ¿Qué veo hoy? --}}
        @if($suggestion)
            <section class="flex items-center gap-4 rounded-3xl border border-cyan-400/20 bg-gradient-to-r from-cyan-500/10 to-violet-500/10 p-4 backdrop-blur-xl">
                <img src="{{ $suggestion->anime->image_url }}" alt="{{ $suggestion->anime->title }}" class="h-16 w-12 rounded-xl object-cover">
                <div class="min-w-0 flex-1">
                    <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-cyan-400">🎲 ¿Qué veo hoy?</p>
                    <a href="{{ route('catalog.show', $suggestion->anime->mal_id) }}" class="block truncate text-sm font-bold text-white hover:text-cyan-400">
                        {{ $suggestion->anime->title }}
                    </a>
                </div>
                <a href="{{ route('mylist.index') }}" title="Otra sugerencia" class="text-2xl transition hover:scale-125">🎲</a>
            </section>
        @endif

        {{-- 🔍 Buscador en mi lista --}}
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-500">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-5.2-5.2M10 17a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
            </span>
            <input type="text" id="buscar-lista" placeholder="Buscar en mi lista..."
                   class="h-12 w-full rounded-2xl border border-[#273244] bg-slate-900/75 pl-11 pr-4 text-sm text-slate-200 outline-none backdrop-blur placeholder:text-slate-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
        </div>

        {{-- 📥 Importar + 💾 Restaurar --}}
        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 backdrop-blur-xl">
                <h3 class="text-sm font-bold text-white">📥 Importar mi historial</h3>
                <p class="mt-1 text-xs text-slate-500">Trae tu lista desde AniList o MyAnimeList</p>
                <form method="POST" action="{{ route('mylist.import') }}" class="mt-4 flex flex-wrap gap-2">
                    @csrf
                    <select name="source" class="h-10 cursor-pointer rounded-xl border border-[#273244] bg-[#111827]/80 px-3 text-xs text-slate-200 outline-none focus:border-cyan-400">
                        <option value="anilist">AniList</option>
                        <option value="mal">MyAnimeList</option>
                    </select>
                    <input type="text" name="mal_username" required placeholder="Tu username"
                           class="h-10 min-w-[140px] flex-1 rounded-xl border border-[#273244] bg-[#111827]/80 px-3 text-xs text-slate-200 outline-none placeholder:text-slate-500 focus:border-cyan-400">
                    <button type="submit" class="h-10 rounded-xl bg-gradient-to-r from-pink-500 to-violet-500 px-4 text-xs font-bold text-white shadow-lg shadow-pink-500/20 transition hover:scale-[1.02]">
                        Importar
                    </button>
                </form>
            </div>
            <div class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 backdrop-blur-xl">
                <h3 class="text-sm font-bold text-white">💾 Restaurar backup</h3>
                <p class="mt-1 text-xs text-slate-500">Sube un archivo .json exportado previamente</p>
                <form method="POST" action="{{ route('mylist.importJson') }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="file" name="backup" accept=".json" required class="flex-1 text-xs text-slate-400 file:mr-3 file:cursor-pointer file:rounded-xl file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-200 hover:file:bg-slate-700">
                    <button type="submit" class="h-10 rounded-xl border border-cyan-400/20 px-4 text-xs font-bold text-cyan-400 transition hover:bg-cyan-400/10">
                        ⬆️ Restaurar
                    </button>
                </form>
            </div>
        </section>

        {{-- LISTA POR ESTADO --}}
        @forelse($items as $status => $group)
            <section>
                <div class="mb-4 flex items-center gap-3">
                    <span class="size-2.5 rounded-full" style="background: {{ $statusHex[$status] ?? '#64748b' }}"></span>
                    <h2 class="text-lg font-bold text-white">{{ \App\Models\UserAnime::STATUS_LABELS[$status] ?? $status }}</h2>
                    <span class="rounded-full bg-slate-800 px-2.5 py-0.5 font-mono text-[10px] text-slate-400">{{ $group->count() }}</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($group as $item)
                        <article class="item-lista flex gap-4 rounded-3xl border border-slate-700/80 bg-slate-900/75 p-4 backdrop-blur-xl transition hover:border-pink-400/30"
                                 data-title="{{ mb_strtolower($item->anime->title) }}">
                            <a href="{{ route('catalog.show', $item->anime->mal_id) }}" class="shrink-0">
                                <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}" class="h-28 w-20 rounded-xl object-cover">
                            </a>

                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-sm font-bold text-white">
                                    {{ $item->anime->title }}
                                    @if($item->rewatch_count > 0)
                                        <span class="ml-1 font-mono text-xs text-violet-400">🔁 x{{ $item->rewatch_count + 1 }}</span>
                                    @endif
                                </h3>
                                <p class="mt-1 font-mono text-xs text-slate-500">
                                    <span class="text-amber-400">★ {{ $item->score ?? '—' }}</span>/10 ·
                                    {{ $item->episodes_watched }} eps @if($item->anime->episodes_total) / {{ $item->anime->episodes_total }} @endif
                                </p>

                                @if($item->anime->episodes_total)
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-pink-500" style="width: {{ $item->progressPercent() }}%"></div>
                                    </div>
                                    <p class="mt-1 font-mono text-[10px] text-slate-600">{{ $item->progressPercent() }}%</p>
                                @endif

                                @if($item->notes)
                                    <p class="mt-2 text-xs italic text-slate-500">📝 {{ $item->notes }}</p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('mylist.update', $item) }}">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()"
                                                class="h-8 cursor-pointer rounded-lg border border-[#273244] bg-[#111827]/80 px-2 text-[11px] text-slate-300 outline-none focus:border-cyan-400">
                                            @foreach(\App\Models\UserAnime::STATUS_LABELS as $value => $label)
                                                <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>

                                    @if($item->status !== 'completed')
                                        <form method="POST" action="{{ route('mylist.increment', $item) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-cyan-400/20 px-3 py-1 font-mono text-[11px] text-cyan-400 transition hover:bg-cyan-400/10">
                                                +1 episodio
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('mylist.rewatch', $item) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-violet-400/20 px-3 py-1 font-mono text-[11px] text-violet-400 transition hover:bg-violet-400/10">
                                                🔁 Lo volví a ver
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('mylist.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 text-[11px] text-red-400 transition hover:text-red-300">Quitar</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-16 text-center backdrop-blur-xl">
                <p class="text-5xl">🎌</p>
                <h3 class="mt-4 text-xl font-bold text-white">Tu lista está vacía</h3>
                <p class="mt-2 text-sm text-slate-500">Explora el catálogo o importa tu historial de MAL.</p>
                <a href="{{ route('catalog.index') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-pink-500 to-violet-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-pink-500/20 transition hover:scale-[1.02]">
                    Ir al catálogo →
                </a>
            </section>
        @endforelse
    </div>

    {{-- 🔍 Filtro en vivo --}}
    <script>
        const q = document.getElementById('buscar-lista');
        if (q) {
            q.addEventListener('input', () => {
                const t = q.value.toLowerCase();
                document.querySelectorAll('.item-lista').forEach(el => {
                    el.style.display = el.dataset.title.includes(t) ? '' : 'none';
                });
            });
        }
    </script>
</x-app-layout>