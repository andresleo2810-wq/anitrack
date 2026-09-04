<x-app-layout>
    @php
        $nombre = explode(' ', auth()->user()->name)[0];
        $h = intdiv($stats['minutes'], 60);
        $tiempo = $h >= 24 ? intdiv($h, 24) . 'd ' . ($h % 24) . 'h' : $h . 'h';

        $colors = [
            'watching' => 'text-cyan-400',
            'completed' => 'text-emerald-400',
            'on_hold' => 'text-amber-400',
            'dropped' => 'text-pink-400',
            'plan_to_watch' => 'text-violet-400',
        ];

        $statusLabels = [
            'watching' => 'Viendo',
            'completed' => 'Completados',
            'on_hold' => 'En pausa',
            'dropped' => 'Abandonados',
            'plan_to_watch' => 'Pendientes',
        ];

        $statusTotal = max($stats['total'], 1);

        $order = ['completed', 'watching', 'plan_to_watch', 'on_hold', 'dropped'];
        $hex = ['completed' => '#34d399', 'watching' => '#22d3ee', 'plan_to_watch' => '#a78bfa', 'on_hold' => '#fbbf24', 'dropped' => '#ec4899'];
        $acc = 0;
        $stops = [];
        foreach ($order as $st) {
            $count = $byStatus[$st] ?? 0;
            if (!$count) continue;
            $from = round($acc / $statusTotal * 100, 1);
            $acc += $count;
            $to = round($acc / $statusTotal * 100, 1);
            $stops[] = "{$hex[$st]} {$from}% {$to}%";
        }
        $conic = 'conic-gradient(' . implode(', ', $stops) . ')';
    @endphp

    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- HERO (alineado con botón a la derecha) --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="flex flex-col gap-3">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-400">
                    {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                </p>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Hola, {{ $nombre }} <span class="text-pink-400">✦</span>
                </h1>
                <p class="text-sm text-slate-500">Tu universo anime, todo en un solo lugar.</p>
            </div>
            <a href="{{ route('recap') }}"
               class="inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-pink-500 to-violet-500 px-6 py-3 font-mono text-sm font-bold text-white shadow-xl shadow-pink-500/20 transition hover:scale-[1.02] hover:shadow-pink-500/40 focus-visible:ring-2 focus-visible:ring-pink-400">
                Ver tu recap {{ now()->year }} <span aria-hidden="true">→</span>
            </a>
        </section>

        {{-- STATS --}}
        <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            <article class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 shadow-2xl shadow-black/10 backdrop-blur-xl">
                <div class="mb-5 flex size-9 items-center justify-center rounded-full bg-cyan-400/15 text-cyan-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15ZM4 20.5A2.5 2.5 0 0 1 6.5 18H20M8 7h8M8 10h6"/></svg>
                </div>
                <p class="font-mono text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                <p class="mt-1 font-mono text-xs text-slate-500">En mi lista</p>
            </article>
            <article class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 shadow-2xl shadow-black/10 backdrop-blur-xl">
                <div class="mb-5 flex size-9 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                </div>
                <p class="font-mono text-2xl font-bold text-white">{{ $stats['completed'] }}</p>
                <p class="mt-1 font-mono text-xs text-slate-500">Completados</p>
            </article>
            <article class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 shadow-2xl shadow-black/10 backdrop-blur-xl">
                <div class="mb-5 flex size-9 items-center justify-center rounded-full bg-pink-400/15 text-pink-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                </div>
                <p class="font-mono text-2xl font-bold text-white">{{ $stats['watching'] }}</p>
                <p class="mt-1 font-mono text-xs text-slate-500">Viendo ahora</p>
            </article>
            <article class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 shadow-2xl shadow-black/10 backdrop-blur-xl">
                <div class="mb-5 flex size-9 items-center justify-center rounded-full bg-amber-400/15 text-amber-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z"/></svg>
                </div>
                <p class="font-mono text-2xl font-bold text-white">{{ $stats['episodes'] }}</p>
                <p class="mt-1 font-mono text-xs text-slate-500">Episodios</p>
            </article>
            <article class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 shadow-2xl shadow-black/10 backdrop-blur-xl">
                <div class="mb-5 flex size-9 items-center justify-center rounded-full bg-violet-400/15 text-violet-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M12 7v5l3 2"/></svg>
                </div>
                <p class="font-mono text-2xl font-bold text-white">{{ $tiempo }}</p>
                <p class="mt-1 font-mono text-xs text-slate-500">Tiempo invertido</p>
            </article>
            <article class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-5 shadow-2xl shadow-black/10 backdrop-blur-xl">
                <div class="mb-5 flex size-9 items-center justify-center rounded-full bg-blue-400/15 text-blue-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6 6.2 8.7 12 3Z"/></svg>
                </div>
                <p class="font-mono text-2xl font-bold text-white">{{ $stats['avg_score'] }}</p>
                <p class="mt-1 font-mono text-xs text-slate-500">Puntuación media</p>
            </article>
        </section>

        {{-- 🔔 AVISOS (banner con campana, estilo v0) --}}
        @if($avisos->isNotEmpty())
            <section class="flex items-center gap-4 rounded-3xl border border-pink-400/10 bg-pink-500/5 p-4 backdrop-blur-xl">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-pink-500/10 text-pink-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-white">Tienes {{ $avisos->count() }} episodio(s) nuevo(s) por ver</p>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $avisos->pluck('title')->implode(', ') }} se actualizaron esta semana.</p>
                </div>
                <span class="text-pink-400" aria-hidden="true">→</span>
            </section>
        @endif

        {{-- CONTINUAR VIENDO + ESTADOS --}}
        <div class="grid gap-6 xl:grid-cols-[1.55fr_1fr]">
            <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-6 backdrop-blur-xl">
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-white">Continuar viendo</h2>
                        <p class="mt-1 text-xs text-slate-500">Justo donde lo dejaste</p>
                    </div>
                    <a href="{{ route('mylist.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300">Ver todo →</a>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @forelse($watching as $item)
                        <article class="rounded-2xl bg-slate-950/60 p-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('catalog.show', $item->anime->mal_id) }}" class="shrink-0">
                                    <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}" class="size-14 rounded-xl object-cover">
                                </a>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-sm font-bold text-white">{{ $item->anime->title }}</h3>
                                    <p class="mt-1 text-xs text-slate-500">Ep {{ $item->episodes_watched }} de {{ $item->anime->episodes_total ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="mt-3 h-1 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-pink-500" style="width: {{ $item->progressPercent() }}%"></div>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <form method="POST" action="{{ route('mylist.increment', $item) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-cyan-400 transition hover:text-cyan-300 focus-visible:ring-2 focus-visible:ring-cyan-400">+ 1 episodio</button>
                                </form>
                                <span class="font-mono text-[10px] text-slate-600">{{ $item->progressPercent() }}%</span>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">Nada en emisión por ahora.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-6 backdrop-blur-xl">
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-white">Estados de tu lista</h2>
                    <p class="mt-1 text-xs text-slate-500">Tu colección de anime</p>
                </div>
                <div class="flex flex-col items-center gap-8 sm:flex-row sm:justify-center">
                    <div class="relative size-44 shrink-0">
                        <div class="absolute inset-0 rounded-full" style="background: {{ $conic }}"></div>
                        <div class="absolute inset-4 flex flex-col items-center justify-center rounded-full bg-slate-900">
                            <span class="font-mono text-3xl font-bold text-white">{{ $stats['total'] }}</span>
                            <span class="font-mono text-xs text-slate-500">títulos</span>
                        </div>
                    </div>
                    <div class="flex w-full flex-col gap-3">
                        @foreach($byStatus as $status => $count)
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="size-2 rounded-full bg-current {{ $colors[$status] ?? 'text-slate-400' }}"></span>
                                    <span class="font-mono text-xs text-slate-400">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                                </div>
                                <span class="font-mono text-xs font-bold text-white">{{ round(($count / $statusTotal) * 100) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        {{-- 🤖 RECOMENDACIONES --}}
        <section>
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <div class="mb-2 inline-flex rounded-full border border-pink-400/20 bg-pink-400/10 px-3 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-pink-300">IA personalizada</div>
                    <h2 class="text-lg font-bold text-white">Recomendaciones para ti</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300">Explorar más →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($recommendations as $rec)
                    <a href="{{ route('catalog.show', $rec['mal_id']) }}" class="group overflow-hidden rounded-3xl border border-slate-700/80 bg-slate-900/75 backdrop-blur-xl transition hover:-translate-y-1 hover:border-pink-400/40">
                        <img src="{{ $rec['images']['jpg']['image_url'] }}" alt="{{ $rec['title'] }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-105">
                        <div class="p-4">
                            <h3 class="truncate text-sm font-bold text-white">{{ $rec['title'] }}</h3>
                            <p class="mt-2 font-mono text-xs text-amber-400">★ {{ $rec['score'] ?? 'N/A' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- 🏆 TOP PUNTUADOS --}}
        <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-6 backdrop-blur-xl">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-white">Top puntuados</h2>
                    <p class="mt-1 text-xs text-slate-500">Los favoritos de tu colección</p>
                </div>
                <a href="{{ route('mylist.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300">Ver mi lista →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($topRated as $item)
                    <article class="flex items-center gap-4 rounded-2xl bg-slate-950/60 p-3">
                        <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}" class="size-16 rounded-xl object-cover">
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-bold text-white">{{ $item->anime->title }}</h3>
                            <p class="mt-2 font-mono text-xs text-amber-400">★ {{ $item->score }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- 📅 CALENDARIO SEMANAL --}}
        @if(!empty($schedule) && count(array_filter($schedule)) > 0)
            <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-6 backdrop-blur-xl">
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-white">Esta semana en emisión</h2>
                    <p class="mt-1 text-xs text-slate-500">Calendario de estrenos</p>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7">
                    @foreach($schedule as $dia => $animes)
                        <div class="rounded-2xl bg-slate-950/60 p-3">
                            <h3 class="mb-2 font-mono text-xs font-bold uppercase tracking-wider text-cyan-400">{{ $dia }}</h3>
                            <div class="space-y-2">
                                @forelse($animes as $a)
                                    <a href="{{ route('catalog.show', $a['mal_id']) }}" class="flex items-center gap-2 group">
                                        <img src="{{ $a['image_url'] }}" alt="{{ $a['title'] }}" class="h-12 w-8 rounded object-cover">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-medium text-slate-200 group-hover:text-cyan-400">{{ $a['title'] }}</p>
                                            @if($a['score'])<p class="font-mono text-[10px] text-amber-400">★ {{ $a['score'] }}</p>@endif
                                        </div>
                                    </a>
                                @empty
                                    <p class="text-[11px] italic text-slate-600">Sin estrenos</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 🎭 GÉNEROS --}}
        <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-6 backdrop-blur-xl">
            <h2 class="mb-6 text-lg font-bold text-white">Tus géneros favoritos</h2>
            <canvas id="genreChart" height="90"></canvas>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('genreChart'), {
            type: 'bar',
            data: {
                labels: @json($byGenre->keys()->values()),
                datasets: [{ data: @json($byGenre->values()->values()), backgroundColor: ['#22d3ee', '#ec4899', '#a78bfa', '#fbbf24', '#34d399', '#f87171'], borderRadius: 8 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { stepSize: 1, color: '#64748b' }, grid: { color: '#273244' } }, x: { ticks: { color: '#94a3b8' }, grid: { display: false } } } }
        });
    </script>
</x-app-layout>