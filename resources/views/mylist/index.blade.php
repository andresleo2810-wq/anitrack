<x-app-layout>
    @php
        $statusHex = [
            'watching' => '#22d3ee',
            'completed' => '#34d399',
            'plan_to_watch' => '#a78bfa',
            'on_hold' => '#fbbf24',
            'dropped' => '#ec4899',
        ];
        $field = 'h-10 w-full rounded-xl border border-white/10 bg-[#0b0d1c] px-3 text-xs text-slate-200 outline-none transition placeholder:text-slate-600 focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20';
    @endphp

    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- HERO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="flex flex-col gap-3">
                <p class="font-mono text-[11px] font-bold uppercase tracking-[0.3em] text-cyan-400">Tu colección personal</p>
                <h1 class="flex items-center gap-3 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Mi Lista
                    <span class="inline-block size-3 rounded-full bg-gradient-to-br from-pink-400 to-fuchsia-500 shadow-lg shadow-pink-500/60" aria-hidden="true"></span>
                </h1>
                <p class="max-w-lg text-sm text-slate-400">Gestiona tu progreso, puntuaciones y rewatches.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('mylist.export') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition hover:scale-[1.02] hover:shadow-emerald-500/40">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Backup
                </a>
                <a href="{{ route('mylist.trash') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-semibold text-slate-400 transition hover:border-red-400/40 hover:bg-red-500/10 hover:text-red-400">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.14 5.32a1.875 1.875 0 00-1.8-1.32H7.66a1.875 1.875 0 00-1.8 1.32l-.82 3.99m13.5-3.99H5.25"/></svg>
                    Papelera
                </a>
            </div>
        </section>

        {{-- Flash success --}}
        @if(session('success'))
            <div class="relative overflow-hidden rounded-2xl border border-emerald-400/30 bg-gradient-to-r from-emerald-500/10 via-[#111528] to-[#111528] p-4 text-sm text-emerald-300 backdrop-blur-xl">
                <div class="relative flex items-center gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                    </span>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- 🎲 ¿Qué veo hoy? --}}
        @if($suggestion)
            <section class="relative overflow-hidden rounded-2xl border border-cyan-400/30 bg-gradient-to-r from-cyan-500/10 via-[#111528] to-violet-500/5 p-4 backdrop-blur-xl">
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-cyan-500/15 blur-3xl"></div>
                <div class="relative flex items-center gap-4">
                    <a href="{{ route('catalog.show', $suggestion->anime->mal_id) }}" class="shrink-0">
                        <img src="{{ $suggestion->anime->image_url }}" alt="{{ $suggestion->anime->title }}" class="h-20 w-14 rounded-xl object-cover ring-1 ring-white/10">
                    </a>
                    <div class="min-w-0 flex-1">
                        <p class="font-mono text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-400">🎲 ¿Qué veo hoy?</p>
                        <a href="{{ route('catalog.show', $suggestion->anime->mal_id) }}" class="mt-1 block truncate text-sm font-bold text-white transition hover:text-cyan-400">
                            {{ $suggestion->anime->title }}
                        </a>
                    </div>
                    <a href="{{ route('mylist.index') }}" title="Otra sugerencia"
                       class="flex size-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-violet-500 text-xl text-white shadow-lg shadow-cyan-500/30 transition hover:scale-110 hover:rotate-180">
                        🎲
                    </a>
                </div>
            </section>
        @endif

        {{-- 🔍 Buscador en mi lista --}}
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-500">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-5.2-5.2M10 17a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
            </span>
            <input type="text" id="buscar-lista" placeholder="Buscar en mi lista..."
                   class="h-12 w-full rounded-2xl border border-white/10 bg-[#111528]/80 pl-11 pr-4 text-sm text-slate-200 outline-none backdrop-blur placeholder:text-slate-500 transition focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
        </div>

        {{-- 📥 Importar + 💾 Restaurar --}}
        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-white/5 bg-[#111528]/80 p-5 backdrop-blur-xl">
                <h3 class="flex items-center gap-2.5 text-sm font-bold text-white">
                    <span class="h-4 w-1 rounded-full bg-gradient-to-b from-pink-400 to-violet-500"></span>
                    Importar mi historial
                </h3>
                <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Trae tu lista desde AniList o MyAnimeList</p>
                <form method="POST" action="{{ route('mylist.import') }}" class="mt-4 flex flex-wrap gap-2">
                    @csrf
                    <select name="source" class="h-10 cursor-pointer rounded-xl border border-white/10 bg-[#0b0d1c] px-3 text-xs text-slate-200 outline-none transition focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
                        <option value="anilist">AniList</option>
                        <option value="mal">MyAnimeList</option>
                    </select>
                    <input type="text" name="mal_username" required placeholder="Tu username"
                           class="h-10 min-w-[140px] flex-1 rounded-xl border border-white/10 bg-[#0b0d1c] px-3 text-xs text-slate-200 outline-none transition placeholder:text-slate-600 focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
                    <button type="submit" class="h-10 rounded-xl bg-gradient-to-r from-pink-500 via-fuchsia-500 to-violet-600 px-4 text-xs font-bold text-white shadow-lg shadow-pink-500/25 transition hover:scale-[1.02]">
                        Importar
                    </button>
                </form>
            </div>
            <div class="rounded-2xl border border-white/5 bg-[#111528]/80 p-5 backdrop-blur-xl">
                <h3 class="flex items-center gap-2.5 text-sm font-bold text-white">
                    <span class="h-4 w-1 rounded-full bg-gradient-to-b from-cyan-400 to-violet-500"></span>
                    Restaurar backup
                </h3>
                <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Sube un archivo .json exportado previamente</p>
                <form method="POST" action="{{ route('mylist.importJson') }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="file" name="backup" accept=".json" required
                           class="flex-1 text-xs text-slate-400 file:mr-3 file:cursor-pointer file:rounded-xl file:border-0 file:bg-[#0b0d1c] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-200 file:ring-1 file:ring-white/10 hover:file:bg-slate-800">
                    <button type="submit" class="h-10 rounded-xl border border-cyan-400/30 bg-cyan-500/10 px-4 text-xs font-bold text-cyan-400 transition hover:bg-cyan-500/20">
                        ⬆️ Restaurar
                    </button>
                </form>
            </div>
        </section>

        {{-- LISTA POR ESTADO --}}
        @forelse($items as $status => $group)
            <section>
                <div class="mb-4 flex items-center gap-3">
                    <span class="size-2.5 rounded-full ring-2 ring-white/5" style="background: {{ $statusHex[$status] ?? '#64748b' }}; box-shadow: 0 0 12px {{ $statusHex[$status] ?? '#64748b' }}"></span>
                    <h2 class="text-lg font-bold text-white">{{ \App\Models\UserAnime::STATUS_LABELS[$status] ?? $status }}</h2>
                    <span class="rounded-full bg-white/5 px-2.5 py-0.5 font-mono text-[10px] font-bold text-slate-400">{{ $group->count() }}</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($group as $item)
                        <article class="item-lista flex gap-4 rounded-2xl border border-white/5 bg-[#111528]/80 p-4 backdrop-blur-xl transition hover:border-pink-400/40 hover:shadow-lg hover:shadow-pink-500/10"
                                 data-title="{{ mb_strtolower($item->anime->title) }}">
                            <a href="{{ route('catalog.show', $item->anime->mal_id) }}" class="shrink-0">
                                <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}" class="h-28 w-20 rounded-xl object-cover ring-1 ring-white/5 transition hover:ring-pink-400/40">
                            </a>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="min-w-0 flex-1 truncate text-sm font-bold text-white">
                                        {{ $item->anime->title }}
                                        @if($item->rewatch_count > 0)
                                            <span class="ml-1 rounded-md bg-violet-500/15 px-1.5 py-0.5 font-mono text-[10px] font-bold text-violet-300">🔁 x{{ $item->rewatch_count + 1 }}</span>
                                        @endif
                                    </h3>
                                    <div class="shrink-0 rounded-md bg-amber-400/10 px-2 py-0.5 font-mono text-[11px] font-bold text-amber-400">
                                        ★ {{ $item->score ?? '—' }}
                                    </div>
                                </div>

                                <p class="mt-1 font-mono text-[11px] text-slate-500">
                                    {{ $item->episodes_watched }} eps @if($item->anime->episodes_total) / {{ $item->anime->episodes_total }} @endif
                                </p>

                                @if($item->anime->episodes_total)
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/5">
                                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 via-fuchsia-500 to-pink-500 transition-all duration-500" style="width: {{ $item->progressPercent() }}%"></div>
                                    </div>
                                    <p class="mt-1 font-mono text-[10px] font-bold text-slate-500">{{ $item->progressPercent() }}%</p>
                                @endif

                                @if($item->notes)
                                    <p class="mt-2 rounded-lg bg-white/5 px-3 py-1.5 text-[11px] italic text-slate-400">📝 {{ $item->notes }}</p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('mylist.update', $item) }}">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()"
                                                class="h-8 cursor-pointer rounded-lg border border-white/10 bg-[#0b0d1c] px-2 text-[11px] font-semibold text-slate-300 outline-none transition focus:border-cyan-400/60">
                                            @foreach(\App\Models\UserAnime::STATUS_LABELS as $value => $label)
                                                <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>

                                    @if($item->status !== 'completed')
                                        <form method="POST" action="{{ route('mylist.increment', $item) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3 py-1.5 font-mono text-[11px] font-bold text-cyan-400 transition hover:bg-cyan-500/20">
                                                <svg class="size-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                                                1 ep
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('mylist.rewatch', $item) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-violet-400/30 bg-violet-500/10 px-3 py-1.5 font-mono text-[11px] font-bold text-violet-400 transition hover:bg-violet-500/20">
                                                🔁 Rewatch
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('mylist.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg px-2 py-1.5 font-mono text-[11px] font-bold uppercase tracking-wider text-red-400 transition hover:bg-red-500/10">Quitar</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="relative overflow-hidden rounded-2xl border border-white/5 bg-[#111528]/80 p-16 text-center backdrop-blur-xl">
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-pink-500/10 blur-3xl"></div>
                <div class="relative">
                    <p class="text-5xl">🎌</p>
                    <h3 class="mt-4 text-xl font-bold text-white">Tu lista está vacía</h3>
                    <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-slate-500">Explora el catálogo o importa tu historial de MAL.</p>
                    <a href="{{ route('catalog.index') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-pink-500 via-fuchsia-500 to-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-pink-500/30 transition hover:scale-[1.02]">
                        Ir al catálogo
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </a>
                </div>
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