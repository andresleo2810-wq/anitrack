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

        {{-- 🌟 HERO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
            <div class="flex flex-col gap-3">
                <p class="font-mono text-[11px] font-bold uppercase tracking-[0.3em] text-cyan-400">
                    {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                </p>
                <h1 class="flex items-center gap-3 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Hola, {{ $nombre }}
                    <span class="inline-block size-3 rounded-full bg-gradient-to-br from-pink-400 to-fuchsia-500 shadow-lg shadow-pink-500/60" aria-hidden="true"></span>
                </h1>
                <p class="max-w-lg text-sm text-slate-400">Tu universo anime, todo en un solo lugar.</p>
            </div>
            <a href="{{ route('recap') }}"
               class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-pink-500 via-fuchsia-500 to-violet-600 px-6 py-3 font-mono text-sm font-bold text-white shadow-lg shadow-pink-500/30 transition hover:shadow-pink-500/50 focus-visible:ring-2 focus-visible:ring-pink-400">
                Ver tu recap {{ now()->year }}
                <svg class="size-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>
        </section>

        {{-- 📊 STATS: scroll horizontal en móvil --}}
        <section class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
            <div class="flex w-max min-w-full gap-3 sm:grid sm:w-auto sm:grid-cols-3 xl:grid-cols-6">
                @php
                    $statsCards = [
                        ['value' => $stats['total'],      'label' => 'En mi lista',       'color' => 'cyan',    'icon' => 'M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15ZM4 20.5A2.5 2.5 0 0 1 6.5 18H20M8 7h8M8 10h6'],
                        ['value' => $stats['completed'],  'label' => 'Completados',       'color' => 'emerald', 'icon' => 'm5 12 4 4L19 6'],
                        ['value' => $stats['watching'],   'label' => 'Viendo ahora',      'color' => 'pink',    'icon' => 'M8 5.5v13l11-6.5-11-6.5Z'],
                        ['value' => $stats['episodes'],   'label' => 'Episodios',         'color' => 'amber',   'icon' => 'm13 2-9 12h7l-1 8 9-12h-7l1-8Z'],
                        ['value' => $tiempo,              'label' => 'Tiempo invertido',  'color' => 'violet',  'icon' => 'M12 7v5l3 2', 'circle' => true],
                        ['value' => $stats['avg_score'],  'label' => 'Puntuación media',  'color' => 'blue',    'icon' => 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6 6.2 8.7 12 3Z'],
                    ];
                @endphp
                @foreach($statsCards as $c)
                    <article class="w-44 shrink-0 rounded-2xl border border-white/5 bg-[#111528]/80 p-5 backdrop-blur-xl transition hover:border-white/10 sm:w-auto">
                        <div class="mb-4 flex size-10 items-center justify-center rounded-xl bg-{{ $c['color'] }}-400/10 text-{{ $c['color'] }}-400">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($c['circle'] ?? false)<circle cx="12" cy="12" r="8.5" stroke-width="1.8"/>@endif
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $c['icon'] }}"/>
                            </svg>
                        </div>
                        <p class="font-mono text-3xl font-bold text-white">{{ $c['value'] }}</p>
                        <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">{{ $c['label'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- 🔔 AVISO EPISODIOS --}}
        @if($avisos->isNotEmpty())
            <section class="relative overflow-hidden rounded-2xl border border-pink-400/30 bg-gradient-to-r from-pink-500/10 via-[#111528] to-[#111528] p-5 backdrop-blur-xl">
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-pink-500/20 blur-3xl"></div>
                <div class="relative flex items-center gap-4">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-pink-500/20 text-pink-300">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-white">Tienes {{ $avisos->count() }} episodio(s) nuevo(s) por ver</p>
                        <p class="mt-1 truncate text-xs text-slate-400">{{ $avisos->pluck('title')->implode(', ') }} se actualizaron esta semana.</p>
                    </div>
                    <a href="{{ route('mylist.index') }}" class="shrink-0 flex size-10 items-center justify-center rounded-full bg-pink-500/15 text-pink-300 transition hover:bg-pink-500/25" aria-label="Ir a mi lista">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </a>
                </div>
            </section>
        @endif

        {{-- ▶️ CONTINUAR VIENDO + DONUT --}}
        <div class="grid gap-6 xl:grid-cols-[1.55fr_1fr]">
            <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-white">Continuar viendo</h2>
                        <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Justo donde lo dejaste</p>
                    </div>
                    <a href="{{ route('mylist.index') }}" class="text-xs font-semibold text-cyan-400 transition hover:text-cyan-300">Ver todo →</a>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-2">
                    @forelse($watching as $item)
                        <article class="rounded-2xl border border-white/5 bg-[#0b0d1c] p-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('catalog.show', $item->anime->mal_id) }}" class="shrink-0">
                                    <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}" class="size-16 rounded-xl object-cover ring-1 ring-white/5">
                                </a>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-sm font-bold text-white">{{ $item->anime->title }}</h3>
                                    <p class="mt-1 font-mono text-[11px] text-slate-400">Ep {{ $item->episodes_watched }} / {{ $item->anime->episodes_total ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                                <div class="h-full rounded-full bg-gradient-to-r from-fuchsia-500 via-pink-500 to-violet-500" style="width: {{ $item->progressPercent() }}%"></div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <form method="POST" action="{{ route('mylist.increment', $item) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-cyan-500/10 px-2.5 py-1.5 font-mono text-[11px] font-bold text-cyan-300 transition hover:bg-cyan-500/20 focus-visible:ring-2 focus-visible:ring-cyan-400">
                                        <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
                                        1 episodio
                                    </button>
                                </form>
                                <span class="font-mono text-[11px] font-bold text-slate-400">{{ $item->progressPercent() }}%</span>
                            </div>
                        </article>
                    @empty
                        <p class="col-span-full text-sm text-slate-500">Nada en emisión por ahora.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-white">Estados de tu lista</h2>
                    <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Tu colección de anime</p>
                </div>
                <div class="flex flex-col items-center gap-6 sm:flex-row sm:justify-center sm:gap-8">
                    <div class="relative size-40 shrink-0">
                        <div class="absolute inset-0 rounded-full" style="background: {{ $conic }}"></div>
                        <div class="absolute inset-4 flex flex-col items-center justify-center rounded-full bg-[#0b0d1c] ring-1 ring-white/5">
                            <span class="font-mono text-3xl font-bold text-white">{{ $stats['total'] }}</span>
                            <span class="font-mono text-[10px] uppercase tracking-wider text-slate-500">títulos</span>
                        </div>
                    </div>
                    <div class="flex w-full flex-col gap-2.5">
                        @foreach($byStatus as $status => $count)
                            @if($count > 0)
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="size-2.5 rounded-full bg-current {{ $colors[$status] ?? 'text-slate-400' }}"></span>
                                    <span class="text-xs font-medium text-slate-300">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-[11px] text-slate-500">{{ $count }}</span>
                                    <span class="font-mono text-xs font-bold text-white">{{ round(($count / $statusTotal) * 100) }}%</span>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        {{-- 🤖 RECOMENDACIONES --}}
        @if(!empty($recommendations))
        <section>
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-pink-400/30 bg-gradient-to-r from-pink-500/15 to-violet-500/15 px-3 py-1 font-mono text-[10px] font-bold uppercase tracking-[0.22em] text-pink-300">
                        <svg class="size-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"/></svg>
                        IA personalizada
                    </div>
                    <h2 class="text-lg font-bold text-white">Recomendaciones para ti</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-xs font-semibold text-cyan-400 transition hover:text-cyan-300">Explorar más →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($recommendations as $rec)
                    <a href="{{ route('catalog.show', $rec['mal_id']) }}" class="group overflow-hidden rounded-2xl border border-white/5 bg-[#111528]/80 backdrop-blur-xl transition hover:-translate-y-1 hover:border-pink-400/40 hover:shadow-xl hover:shadow-pink-500/10">
                        <div class="relative overflow-hidden">
                            <img src="{{ $rec['images']['jpg']['image_url'] }}" alt="{{ $rec['title'] }}" class="aspect-[3/4] w-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#111528] via-transparent to-transparent"></div>
                        </div>
                        <div class="p-4">
                            <h3 class="truncate text-sm font-bold text-white">{{ $rec['title'] }}</h3>
                            <p class="mt-2 font-mono text-xs text-amber-400">★ {{ $rec['score'] ?? 'N/A' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- 🏆 TOP PUNTUADOS --}}
        @if(!empty($topRated))
        <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-white">Top puntuados</h2>
                    <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Los favoritos de tu colección</p>
                </div>
                <a href="{{ route('mylist.index') }}" class="text-xs font-semibold text-cyan-400 transition hover:text-cyan-300">Ver mi lista →</a>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($topRated as $item)
                    <article class="flex items-center gap-3 rounded-2xl border border-white/5 bg-[#0b0d1c] p-3">
                        <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}" class="size-16 shrink-0 rounded-xl object-cover ring-1 ring-white/5">
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-bold text-white">{{ $item->anime->title }}</h3>
                            <p class="mt-1 font-mono text-xs font-bold text-amber-400">★ {{ $item->score }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
        @endif

        {{-- 📅 CALENDARIO SEMANAL --}}
        @if(!empty($schedule) && count(array_filter($schedule)) > 0)
            <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-white">Esta semana en emisión</h2>
                    <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Calendario de estrenos</p>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7">
                    @foreach($schedule as $dia => $animes)
                        <div class="rounded-2xl border border-white/5 bg-[#0b0d1c] p-3">
                            <h3 class="mb-3 border-b border-white/5 pb-2 font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-400">{{ $dia }}</h3>
                            <div class="space-y-2">
                                @forelse($animes as $a)
                                    <a href="{{ route('catalog.show', $a['mal_id']) }}" class="flex items-center gap-2 group">
                                        <img src="{{ $a['image_url'] }}" alt="{{ $a['title'] }}" class="h-12 w-8 shrink-0 rounded object-cover">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-[11px] font-medium text-slate-200 transition group-hover:text-cyan-400">{{ $a['title'] }}</p>
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
        @if($byGenre->isNotEmpty())
        <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-white">Tus géneros favoritos</h2>
                <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Lo que más disfrutas</p>
            </div>
            <canvas id="genreChart" height="90"></canvas>
        </section>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if (document.getElementById('genreChart')) {
            new Chart(document.getElementById('genreChart'), {
                type: 'bar',
                data: {
                    labels: @json($byGenre->keys()->values()),
                    datasets: [{
                        data: @json($byGenre->values()->values()),
                        backgroundColor: ['#22d3ee', '#ec4899', '#a78bfa', '#fbbf24', '#34d399', '#f87171'],
                        borderRadius: 10,
                        borderSkipped: false
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { stepSize: 1, color: '#64748b' }, grid: { color: '#1e293b' } },
                        x: { ticks: { color: '#94a3b8', font: { family: 'monospace', size: 10 } }, grid: { display: false } }
                    }
                }
            });
        }
    </script>
</x-app-layout>