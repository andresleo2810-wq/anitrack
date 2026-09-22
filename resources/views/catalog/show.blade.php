<x-app-layout>
    @php
        $field = 'w-full rounded-xl border border-white/10 bg-[#0b0d1c] px-3 py-2 text-sm text-slate-200 outline-none transition placeholder:text-slate-600 focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20';
        $label = 'text-xs font-semibold text-slate-400';
    @endphp
    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- Volver + flashes --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 font-mono text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500 transition hover:text-cyan-400">
                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6 6-6-6 6-6"/></svg>
                Volver al catálogo
            </a>
        </div>

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

        @if($errors->any())
            <div class="rounded-2xl border border-red-400/30 bg-red-500/10 p-4 text-sm text-red-300 backdrop-blur-xl">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- 🎬 HERO con backdrop del poster --}}
        <section class="relative overflow-hidden rounded-2xl border border-white/5">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $anime['images']['jpg']['image_url'] }}')"></div>
            <div class="absolute inset-0 bg-[#070812]/70 backdrop-blur-2xl"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#070812] via-[#070812]/40 to-transparent"></div>

            <div class="relative flex flex-col gap-8 p-6 md:flex-row md:p-10">
                <img src="{{ $anime['images']['jpg']['image_url'] }}" alt="{{ $anime['title'] }}"
                     class="w-44 shrink-0 rounded-2xl object-cover shadow-2xl shadow-pink-500/25 ring-1 ring-white/10 md:w-60">

                <div class="min-w-0 flex-1">
                    <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        {{ $anime['title_spanish'] ?? $anime['title'] }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ !empty($anime['title_spanish']) ? $anime['title'] : ($anime['title_english'] ?? '') }}
                    </p>

                    {{-- Badges --}}
                    <div class="mt-4 flex flex-wrap gap-2 font-mono text-xs">
                        @if(!empty($anime['score']))
                            <span class="inline-flex items-center gap-1 rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 font-bold text-amber-400">
                                <svg class="size-3" fill="currentColor" viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6 6.2 8.7 12 3Z"/></svg>
                                {{ $anime['score'] }}
                            </span>
                        @endif
                        @if(!empty($anime['type']))
                            <span class="rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-cyan-400">{{ $anime['type'] }}</span>
                        @endif
                        @if(!empty($anime['episodes']))
                            <span class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-emerald-400">{{ $anime['episodes'] }} eps</span>
                        @endif
                        @if(!empty($anime['status']))
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-300">{{ $anime['status'] }}</span>
                        @endif
                    </div>

                    {{-- Géneros --}}
                    @if(!empty($anime['genres']))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($anime['genres'] as $genre)
                                @php $name = is_array($genre) ? ($genre['name'] ?? '') : $genre; @endphp
                                @if($name)
                                    <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-[11px] font-medium text-violet-300 ring-1 ring-violet-400/20">{{ $name }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- GRID 2 COLUMNAS --}}
        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_400px]">

            {{-- COLUMNA IZQUIERDA --}}
            <div class="flex min-w-0 flex-col gap-6">

                {{-- Sinopsis --}}
                @if(!empty($anime['synopsis']))
                    <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                        <h2 class="flex items-center gap-2.5 text-lg font-bold text-white">
                            <span class="h-5 w-1 rounded-full bg-gradient-to-b from-pink-400 to-violet-500"></span>
                            Sinopsis
                        </h2>
                        <div id="sinopsis-box" data-locked="{{ (!$userAnime || $userAnime->status !== 'completed') ? '1' : '0' }}">
                            <p id="sinopsis-text" class="mt-3 text-sm leading-relaxed text-slate-400">{{ $anime['synopsis'] }}</p>
                            <button id="btn-spoiler" type="button"
                                    class="mt-3 hidden rounded-full border border-pink-400/30 bg-pink-500/10 px-4 py-1.5 font-mono text-[11px] font-bold uppercase tracking-wider text-pink-300 transition hover:bg-pink-500/20">
                                👁️ Revelar sinopsis
                            </button>
                        </div>
                    </section>
                @endif

                {{-- 🎭 Personajes --}}
                @if(!empty($characters))
                    <section class="min-w-0 rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                        <h2 class="mb-4 flex items-center gap-2.5 text-lg font-bold text-white">
                            <span class="h-5 w-1 rounded-full bg-gradient-to-b from-cyan-400 to-violet-500"></span>
                            Personajes
                        </h2>
                        <div class="flex max-w-full gap-3 overflow-x-auto pb-2">
                            @foreach($characters as $c)
                                <div class="w-28 shrink-0 rounded-2xl border border-white/5 bg-[#0b0d1c] p-2 transition hover:border-cyan-400/30">
                                    <img src="{{ $c['image'] }}" alt="{{ $c['name'] }}" class="h-36 w-full rounded-xl object-cover">
                                    <p class="mt-2 truncate text-xs font-semibold text-slate-200">{{ $c['name'] }}</p>
                                    <p class="font-mono text-[10px] {{ $c['role'] === 'Main' ? 'text-cyan-400' : 'text-slate-500' }}">{{ $c['role'] === 'Main' ? 'Principal' : 'Secundario' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- 🔗 Relacionados --}}
                @if(!empty($relations))
                    <section class="min-w-0">
                        <h2 class="mb-4 flex items-center gap-2.5 text-lg font-bold text-white">
                            <span class="h-5 w-1 rounded-full bg-gradient-to-b from-violet-400 to-pink-500"></span>
                            Relacionados
                        </h2>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach($relations as $r)
                                <a href="{{ route('catalog.show', $r['mal_id']) }}" class="flex items-center gap-3 rounded-2xl border border-white/5 bg-[#0b0d1c] p-2 transition hover:border-pink-400/30 hover:bg-[#111528]">
                                    <img src="{{ $r['image'] }}" alt="{{ $r['title'] }}" class="h-16 w-11 shrink-0 rounded-lg object-cover">
                                    <div class="min-w-0">
                                        <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-cyan-400">{{ $r['relation'] }}</p>
                                        <p class="truncate text-xs font-semibold text-slate-200">{{ $r['title'] }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- 🎵 OP/ED --}}
                @if(!empty($themes['openings']) || !empty($themes['endings']))
                    <section class="min-w-0 rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                        <h2 class="mb-4 flex items-center gap-2.5 text-lg font-bold text-white">
                            <span class="h-5 w-1 rounded-full bg-gradient-to-b from-amber-400 to-pink-500"></span>
                            Openings & Endings
                        </h2>
                        @if(!empty($themes['openings']))
                            <p class="font-mono text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-400">Openings</p>
                            <ul class="mt-2 space-y-1.5 font-mono text-xs text-slate-400">
                                @foreach($themes['openings'] as $op)
                                    <li class="flex items-center gap-2 truncate">
                                        <svg class="size-3 shrink-0 text-cyan-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                                        <span class="truncate">{{ $op }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if(!empty($themes['endings']))
                            <p class="mt-4 font-mono text-[10px] font-bold uppercase tracking-[0.22em] text-pink-400">Endings</p>
                            <ul class="mt-2 space-y-1.5 font-mono text-xs text-slate-400">
                                @foreach($themes['endings'] as $ed)
                                    <li class="flex items-center gap-2 truncate">
                                        <svg class="size-3 shrink-0 text-pink-400" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h12v12H6z"/></svg>
                                        <span class="truncate">{{ $ed }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endif

                {{-- 🖼️ Portadas --}}
                @if(!empty($pictures))
                    <section class="min-w-0">
                        <h2 class="mb-4 flex items-center gap-2.5 text-lg font-bold text-white">
                            <span class="h-5 w-1 rounded-full bg-gradient-to-b from-pink-400 to-cyan-400"></span>
                            Portadas
                        </h2>
                        <div class="flex max-w-full gap-3 overflow-x-auto pb-2">
                            @foreach($pictures as $pic)
                                <img src="{{ $pic }}" alt="Portada" class="h-40 w-28 shrink-0 rounded-xl object-cover ring-1 ring-white/5 transition hover:ring-pink-400/40">
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ✨ Similares --}}
                @if(!empty($similar))
                    <section class="min-w-0">
                        <h2 class="mb-4 flex items-center gap-2.5 text-lg font-bold text-white">
                            <span class="h-5 w-1 rounded-full bg-gradient-to-b from-fuchsia-400 to-violet-500"></span>
                            Si te gustó, prueba con...
                        </h2>
                        <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-5 sm:gap-4">
                            @foreach($similar as $rec)
                                <x-anime-card :mal-id="$rec['mal_id']" :title="$rec['title']" :image="$rec['image']" :score="$rec['score'] ?? null" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- COLUMNA DERECHA --}}
            <div class="flex min-w-0 flex-col gap-6">

                {{-- 📋 Datos técnicos --}}
                <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                    <h2 class="flex items-center gap-2.5 text-sm font-bold text-white">
                        <span class="h-4 w-1 rounded-full bg-gradient-to-b from-cyan-400 to-violet-500"></span>
                        Datos técnicos
                    </h2>
                    <dl class="mt-4 grid grid-cols-2 gap-4 text-xs">
                        @if(!empty($anime['studios']))
                            <div class="min-w-0"><dt class="font-mono text-[10px] uppercase tracking-wider text-slate-500">Estudio</dt><dd class="mt-1 break-words font-semibold text-slate-200">{{ collect($anime['studios'])->pluck('name')->implode(', ') }}</dd></div>
                        @endif
                        @if(!empty($anime['title_japanese']))
                            <div class="min-w-0"><dt class="font-mono text-[10px] uppercase tracking-wider text-slate-500">Título japonés</dt><dd class="mt-1 break-words text-slate-200">{{ $anime['title_japanese'] }}</dd></div>
                        @endif
                        @if(!empty($anime['duration']))
                            <div class="min-w-0"><dt class="font-mono text-[10px] uppercase tracking-wider text-slate-500">Duración</dt><dd class="mt-1 text-slate-200">{{ $anime['duration'] }}</dd></div>
                        @endif
                        @if(!empty($anime['aired']['from']))
                            <div class="min-w-0"><dt class="font-mono text-[10px] uppercase tracking-wider text-slate-500">Emitido</dt><dd class="mt-1 text-slate-200">{{ \Carbon\Carbon::parse($anime['aired']['from'])->format('d-m-Y') }}</dd></div>
                        @endif
                        @if(!empty($anime['aired']['to']))
                            <div class="min-w-0"><dt class="font-mono text-[10px] uppercase tracking-wider text-slate-500">Finalizado</dt><dd class="mt-1 text-slate-200">{{ \Carbon\Carbon::parse($anime['aired']['to'])->format('d-m-Y') }}</dd></div>
                        @endif
                        @if(!empty($anime['season']))
                            <div class="min-w-0"><dt class="font-mono text-[10px] uppercase tracking-wider text-slate-500">Temporada</dt><dd class="mt-1 text-slate-200">{{ ucfirst($anime['season']) }} {{ $anime['year'] ?? '' }}</dd></div>
                        @endif
                        @if(!empty($anime['members']))
                            <div class="min-w-0"><dt class="font-mono text-[10px] uppercase tracking-wider text-slate-500">Visitas</dt><dd class="mt-1 font-mono font-bold text-cyan-400">{{ number_format($anime['members'] / 1000000, 1) }}M</dd></div>
                        @endif
                    </dl>
                </section>

                {{-- ⚖️ Score vs comunidad --}}
                @if($userAnime && $userAnime->score && !empty($anime['score']))
                    <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                        <h2 class="flex items-center gap-2.5 text-sm font-bold text-white">
                            <span class="h-4 w-1 rounded-full bg-gradient-to-b from-amber-400 to-pink-500"></span>
                            Tu opinión vs la comunidad
                        </h2>
                        <div class="mt-4 space-y-4">
                            <div>
                                <div class="mb-1 flex justify-between font-mono text-xs">
                                    <span class="text-slate-400">Comunidad</span>
                                    <span class="font-bold text-amber-400">★ {{ $anime['score'] }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-white/5">
                                    <div class="h-full rounded-full bg-amber-400" style="width: {{ min(100, $anime['score'] * 10) }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 flex justify-between font-mono text-xs">
                                    <span class="text-slate-400">Tú</span>
                                    <span class="font-bold text-pink-400">★ {{ $userAnime->score }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-white/5">
                                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 via-fuchsia-500 to-pink-500" style="width: {{ $userAnime->score * 10 }}%"></div>
                                </div>
                            </div>
                        </div>
                        @php $diff = round($userAnime->score - $anime['score'], 1); @endphp
                        <p class="mt-3 text-xs text-slate-500">
                            @if($diff > 0.5) 💖 Te gustó {{ $diff }} puntos MÁS que a la comunidad
                            @elseif($diff < -0.5) 🤷 Te gustó {{ abs($diff) }} puntos MENOS que a la comunidad
                            @else 🤝 Opinión muy similar a la comunidad
                            @endif
                        </p>
                    </section>
                @endif

                {{-- 💾 Formulario Mi Lista PRO --}}
                <section id="mylist-form" data-total-eps="{{ $anime['episodes'] ?? 0 }}"
                         class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl">
                    <h2 class="flex items-center gap-2.5 text-sm font-bold text-white">
                        <span class="h-4 w-1 rounded-full bg-gradient-to-b from-pink-400 to-fuchsia-500"></span>
                        {{ $userAnime ? 'Actualizar mi lista' : 'Agregar a mi lista' }}
                    </h2>

                    <form method="POST" action="{{ route('mylist.store') }}" class="mt-4 space-y-5">
                        @csrf
                        <input type="hidden" name="mal_id" value="{{ $anime['mal_id'] }}">
                        <input type="hidden" name="title" value="{{ $anime['title'] }}">
                        <input type="hidden" name="image_url" value="{{ $anime['images']['jpg']['image_url'] ?? null }}">
                        <input type="hidden" name="score_api" value="{{ $anime['score'] ?? null }}">
                        <input type="hidden" name="episodes_api" value="{{ $anime['episodes'] ?? null }}">
                        <input type="hidden" name="genres" value="{{ json_encode(collect($anime['genres'] ?? [])->map(fn($g) => is_array($g) ? ($g['name'] ?? '') : $g)->values()) }}">

                        {{-- Estado --}}
                        <div>
                            <label class="{{ $label }}">Estado</label>
                            <select name="status" id="status-select" class="mt-1 w-full cursor-pointer rounded-xl border border-white/10 bg-[#0b0d1c] px-3 py-2 text-sm text-slate-200 outline-none transition focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
                                @foreach(\App\Models\UserAnime::STATUS_LABELS as $value => $labelOpt)
                                    <option value="{{ $value }}" {{ ($userAnime->status ?? 'plan_to_watch') === $value ? 'selected' : '' }}>{{ $labelOpt }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ⭐ Puntuación neón 1-10 --}}
                        <div>
                            <label class="{{ $label }}">Tu puntuación</label>
                            <input type="hidden" name="score" id="score-input" value="{{ $userAnime->score ?? '' }}">
                            <div class="mt-2 grid grid-cols-10 gap-1">
                                @for($i = 1; $i <= 10; $i++)
                                    <button type="button" data-score="{{ $i }}"
                                            class="score-btn flex h-9 items-center justify-center rounded-lg border border-white/10 bg-[#0b0d1c] font-mono text-xs font-bold text-slate-500 transition hover:scale-105 hover:border-amber-400/50 hover:text-amber-300">
                                        {{ $i }}
                                    </button>
                                @endfor
                            </div>
                            <p id="score-label" class="mt-1.5 font-mono text-[10px] text-slate-500">Sin puntuar</p>
                        </div>

                        {{-- 🎚️ Episodios con stepper + progreso --}}
                        <div>
                            <div class="flex items-center justify-between">
                                <label class="{{ $label }}">Episodios vistos</label>
                                <span id="eps-label" class="font-mono text-[10px] font-bold text-cyan-400"></span>
                            </div>
                            <input type="hidden" name="episodes_watched" id="eps-input" value="{{ $userAnime->episodes_watched ?? 0 }}">

                            <div class="mt-2 flex items-center gap-2">
                                <button type="button" id="eps-minus"
                                        class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-[#0b0d1c] font-mono text-lg font-bold text-slate-300 transition hover:border-cyan-400/50 hover:text-cyan-300">−</button>
                                <div id="eps-display" class="flex-1 text-center font-mono text-lg font-bold text-white">0</div>
                                <button type="button" id="eps-plus"
                                        class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-[#0b0d1c] font-mono text-lg font-bold text-slate-300 transition hover:border-cyan-400/50 hover:text-cyan-300">+</button>
                            </div>

                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/5">
                                <div id="eps-bar" class="h-full rounded-full bg-gradient-to-r from-cyan-400 via-fuchsia-500 to-pink-500 transition-all duration-300" style="width: 0%"></div>
                            </div>

                            <div class="mt-2 flex gap-2">
                                <button type="button" data-eps-add="1"
                                        class="eps-quick flex-1 rounded-lg border border-white/10 bg-[#0b0d1c] py-2 font-mono text-[11px] font-bold text-slate-400 transition hover:border-cyan-400/50 hover:text-cyan-300">+1</button>
                                <button type="button" data-eps-add="5"
                                        class="eps-quick flex-1 rounded-lg border border-white/10 bg-[#0b0d1c] py-2 font-mono text-[11px] font-bold text-slate-400 transition hover:border-cyan-400/50 hover:text-cyan-300">+5</button>
                                <button type="button" id="eps-max"
                                        class="flex-1 rounded-lg border border-pink-400/30 bg-pink-500/10 py-2 font-mono text-[11px] font-bold text-pink-300 transition hover:bg-pink-500/20">⏭ MAX</button>
                            </div>
                        </div>

                        {{-- Notas --}}
                        <div>
                            <label class="{{ $label }}">Notas personales</label>
                            <textarea name="notes" rows="2" placeholder="Ej: ver manga después, el OST es increíble..."
                                      class="mt-1 {{ $field }}">{{ $userAnime->notes ?? '' }}</textarea>
                        </div>

                        {{-- Fechas --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $label }}">Empezado</label>
                                <input type="date" name="started_at" value="{{ $userAnime?->started_at?->toDateString() }}" class="mt-1 {{ $field }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Terminado</label>
                                <input type="date" name="finished_at" value="{{ $userAnime?->finished_at?->toDateString() }}" class="mt-1 {{ $field }}">
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex flex-wrap gap-3 pt-1">
                            <button type="submit"
                                    class="flex-1 rounded-2xl bg-gradient-to-r from-pink-500 via-fuchsia-500 to-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-pink-500/30 transition hover:scale-[1.02] focus-visible:ring-2 focus-visible:ring-pink-400">
                                {{ $userAnime ? '💾 Actualizar mi lista' : '+ Agregar a mi lista' }}
                            </button>

                            @php $trailer = $anime['trailer_url'] ?? ($anime['trailer']['url'] ?? null); @endphp
                            @if($trailer)
                                <a href="{{ $trailer }}" target="_blank"
                                   class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-red-400 transition hover:border-red-400/40 hover:bg-red-500/10">
                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                                    Trailer
                                </a>
                            @endif
                        </div>
                    </form>

                    @if($userAnime)
                        <form method="POST" action="{{ route('mylist.destroy', $userAnime) }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-mono text-[11px] font-bold uppercase tracking-wider text-red-400 transition hover:text-red-300">🗑️ Quitar de mi lista</button>
                        </form>
                    @endif
                </section>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const box = document.getElementById('sinopsis-box');
        if (!box) return;
        const activo = localStorage.getItem('antispoiler') === '1';
        const locked = box.dataset.locked === '1';
        const texto = document.getElementById('sinopsis-text');
        const btn = document.getElementById('btn-spoiler');

        if (activo && locked) {
            texto.classList.add('blur-md', 'select-none');
            btn.classList.remove('hidden');
            btn.onclick = () => {
                texto.classList.remove('blur-md', 'select-none');
                btn.classList.add('hidden');
            };
        }
    })();

    // 🎨 Formulario PRO: score neón + stepper eps + autocompletado
    (function () {
        const section = document.getElementById('mylist-form');
        if (!section) return;
        const total = parseInt(section.dataset.totalEps) || 0;

        // ⭐ Score 1-10
        const scoreInput = document.getElementById('score-input');
        const scoreLabel = document.getElementById('score-label');
        const btns = section.querySelectorAll('.score-btn');
        const palabras = {1:'😖 Insoportable',2:'😞 Muy malo',3:'😕 Malo',4:'😐 Flojo',5:'😑 Mediocre',6:'🙂 Pasable',7:'😊 Bueno',8:'😍 Muy bueno',9:'🤩 Excelente',10:'🏆 Obra maestra'};

        function paintScore(v) {
            btns.forEach(b => {
                const on = parseInt(b.dataset.score) === v;
                b.className = 'score-btn flex h-9 items-center justify-center rounded-lg font-mono text-xs font-bold transition hover:scale-105 ' + (on
                    ? 'bg-gradient-to-br from-amber-400 to-pink-500 text-white shadow-lg shadow-amber-500/30 scale-110 border-transparent'
                    : 'border border-white/10 bg-[#0b0d1c] text-slate-500 hover:border-amber-400/50 hover:text-amber-300');
            });
            scoreLabel.textContent = v ? palabras[v] + ' · ★' + v : 'Sin puntuar';
        }
        btns.forEach(b => b.onclick = () => {
            const v = parseInt(b.dataset.score);
            scoreInput.value = (parseInt(scoreInput.value) === v) ? '' : v;
            paintScore(parseInt(scoreInput.value) || 0);
        });
        paintScore(parseInt(scoreInput.value) || 0);

        // 🎚️ Episodios
        const epsInput = document.getElementById('eps-input');
        const epsDisplay = document.getElementById('eps-display');
        const epsBar = document.getElementById('eps-bar');
        const epsLabel = document.getElementById('eps-label');

        function paintEps() {
            const v = parseInt(epsInput.value) || 0;
            epsDisplay.textContent = total ? v + ' / ' + total : String(v);
            epsLabel.textContent = total ? Math.round(v / total * 100) + '%' : '';
            epsBar.style.width = total ? Math.min(100, v / total * 100) + '%' : (v > 0 ? '100%' : '0%');
        }
        function setEps(v) {
            v = Math.max(0, v);
            if (total) v = Math.min(total, v);
            epsInput.value = v;
            paintEps();
        }

        document.getElementById('eps-minus').onclick = () => setEps((parseInt(epsInput.value) || 0) - 1);
        document.getElementById('eps-plus').onclick = () => setEps((parseInt(epsInput.value) || 0) + 1);
        section.querySelectorAll('.eps-quick').forEach(b => b.onclick = () => setEps((parseInt(epsInput.value) || 0) + parseInt(b.dataset.epsAdd)));
        document.getElementById('eps-max').onclick = () => setEps(total || 9999);

        // 🤖 Autocompletado por estado
        document.getElementById('status-select').addEventListener('change', function () {
            if (this.value === 'completed' && total) setEps(total);
            if (this.value === 'plan_to_watch') setEps(0);
        });

        paintEps();
    })();
    </script>
</x-app-layout>