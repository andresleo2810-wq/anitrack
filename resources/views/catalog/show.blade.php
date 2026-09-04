<x-app-layout>
    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- Volver + flashes --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('catalog.index') }}" class="text-sm text-slate-500 transition hover:text-cyan-400">← Volver al catálogo</a>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 p-4 text-sm text-emerald-300 backdrop-blur">✅ {{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-red-400/20 bg-red-500/10 p-4 text-sm text-red-300 backdrop-blur">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- HERO con backdrop del poster --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-700/80">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $anime['images']['jpg']['image_url'] }}')"></div>
            <div class="absolute inset-0 bg-[#080d1a]/70 backdrop-blur-2xl"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#080d1a] via-transparent to-transparent"></div>

            <div class="relative flex flex-col gap-8 p-6 md:flex-row md:p-10">
                <img src="{{ $anime['images']['jpg']['image_url'] }}" alt="{{ $anime['title'] }}"
                     class="w-44 shrink-0 rounded-3xl border border-white/10 object-cover shadow-2xl shadow-pink-500/20 md:w-60">

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
                            <span class="rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 font-bold text-amber-400">★ {{ $anime['score'] }}</span>
                        @endif
                        @if(!empty($anime['type']))
                            <span class="rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-cyan-400">{{ $anime['type'] }}</span>
                        @endif
                        @if(!empty($anime['episodes']))
                            <span class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-emerald-400">{{ $anime['episodes'] }} eps</span>
                        @endif
                        @if(!empty($anime['status']))
                            <span class="rounded-full border border-slate-600 bg-slate-800/60 px-3 py-1 text-slate-300">{{ $anime['status'] }}</span>
                        @endif
                    </div>

                    {{-- Géneros --}}
                    @if(!empty($anime['genres']))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($anime['genres'] as $genre)
                                @php $name = is_array($genre) ? ($genre['name'] ?? '') : $genre; @endphp
                                @if($name)
                                    <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-[11px] text-violet-300">{{ $name }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1fr_400px]">
            {{-- COLUMNA IZQ: sinopsis + similares --}}
            <div class="flex flex-col gap-6">
                @if(!empty($anime['synopsis']))
                    <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-6 backdrop-blur-xl">
                        <h2 class="text-lg font-bold text-white">Sinopsis</h2>
                        <div id="sinopsis-box" data-locked="{{ (!$userAnime || $userAnime->status !== 'completed') ? '1' : '0' }}">
                            <p id="sinopsis-text" class="mt-3 text-sm leading-relaxed text-slate-400">{{ $anime['synopsis'] }}</p>
                            <button id="btn-spoiler" type="button"
                                    class="mt-3 hidden rounded-full border border-pink-400/30 bg-pink-500/10 px-4 py-1.5 text-xs font-semibold text-pink-300 transition hover:bg-pink-500/20">
                                👁️ Revelar sinopsis
                            </button>
                        </div>
                    </section>
                @endif

                @if(!empty($similar))
                    <section>
                        <h2 class="mb-4 text-lg font-bold text-white">✨ Si te gustó, prueba con...</h2>
                        <div class="grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-6">
                            @foreach($similar as $rec)
                                <x-anime-card :mal-id="$rec['mal_id']" :title="$rec['title']" :image="$rec['image']" :score="$rec['score'] ?? null" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- COLUMNA DER: score vs comunidad + formulario --}}
            <div class="flex flex-col gap-6">
                @if($userAnime && $userAnime->score && !empty($anime['score']))
                    <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-6 backdrop-blur-xl">
                        <h2 class="text-sm font-bold text-white">⚖️ Tu opinión vs la comunidad</h2>
                        <div class="mt-4 space-y-4">
                            <div>
                                <div class="mb-1 flex justify-between font-mono text-xs">
                                    <span class="text-slate-400">Comunidad</span>
                                    <span class="font-bold text-amber-400">★ {{ $anime['score'] }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full rounded-full bg-amber-400" style="width: {{ min(100, $anime['score'] * 10) }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 flex justify-between font-mono text-xs">
                                    <span class="text-slate-400">Tú</span>
                                    <span class="font-bold text-pink-400">★ {{ $userAnime->score }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-pink-500" style="width: {{ $userAnime->score * 10 }}%"></div>
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

                {{-- Formulario Mi Lista --}}
                <section class="rounded-3xl border border-slate-700/80 bg-slate-900/75 p-6 backdrop-blur-xl">
                    <h2 class="text-sm font-bold text-white">{{ $userAnime ? '💾 Actualizar mi lista' : '+ Agregar a mi lista' }}</h2>

                    <form method="POST" action="{{ route('mylist.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="mal_id" value="{{ $anime['mal_id'] }}">
                        <input type="hidden" name="title" value="{{ $anime['title'] }}">
                        <input type="hidden" name="image_url" value="{{ $anime['images']['jpg']['image_url'] ?? null }}">
                        <input type="hidden" name="score_api" value="{{ $anime['score'] ?? null }}">
                        <input type="hidden" name="episodes_api" value="{{ $anime['episodes'] ?? null }}">
                        <input type="hidden" name="genres" value="{{ json_encode(collect($anime['genres'] ?? [])->map(fn($g) => is_array($g) ? ($g['name'] ?? '') : $g)->values()) }}">

                        <div>
                            <label class="text-xs font-semibold text-slate-400">Estado</label>
                            <select name="status" class="mt-1 w-full cursor-pointer rounded-xl border border-[#273244] bg-[#111827]/80 px-3 py-2 text-sm text-slate-200 outline-none focus:border-cyan-400">
                                @foreach(\App\Models\UserAnime::STATUS_LABELS as $value => $label)
                                    <option value="{{ $value }}" {{ ($userAnime->status ?? 'plan_to_watch') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-400">Tu puntuación (1-10)</label>
                                <input type="number" name="score" min="1" max="10" value="{{ $userAnime->score ?? '' }}"
                                       class="mt-1 w-full rounded-xl border border-[#273244] bg-[#111827]/80 px-3 py-2 text-sm text-slate-200 outline-none focus:border-cyan-400">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-400">Episodios vistos</label>
                                <input type="number" name="episodes_watched" min="0" value="{{ $userAnime->episodes_watched ?? 0 }}"
                                       class="mt-1 w-full rounded-xl border border-[#273244] bg-[#111827]/80 px-3 py-2 text-sm text-slate-200 outline-none focus:border-cyan-400">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-400">📝 Notas personales</label>
                            <textarea name="notes" rows="2" placeholder="Ej: ver manga después, el OST es increíble..."
                                      class="mt-1 w-full rounded-xl border border-[#273244] bg-[#111827]/80 px-3 py-2 text-sm text-slate-200 outline-none placeholder:text-slate-600 focus:border-cyan-400">{{ $userAnime->notes ?? '' }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-400">📅 Empezado</label>
                                <input type="date" name="started_at" value="{{ $userAnime?->started_at?->toDateString() }}"
                                       class="mt-1 w-full rounded-xl border border-[#273244] bg-[#111827]/80 px-3 py-2 text-sm text-slate-200 outline-none focus:border-cyan-400">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-400">🏁 Terminado</label>
                                <input type="date" name="finished_at" value="{{ $userAnime?->finished_at?->toDateString() }}"
                                       class="mt-1 w-full rounded-xl border border-[#273244] bg-[#111827]/80 px-3 py-2 text-sm text-slate-200 outline-none focus:border-cyan-400">
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-1">
                            <button type="submit"
                                    class="flex-1 rounded-2xl bg-gradient-to-r from-pink-500 to-violet-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-pink-500/20 transition hover:scale-[1.02] focus-visible:ring-2 focus-visible:ring-pink-400">
                                {{ $userAnime ? '💾 Actualizar mi lista' : '+ Agregar a mi lista' }}
                            </button>

                            @php $trailer = $anime['trailer_url'] ?? ($anime['trailer']['url'] ?? null); @endphp
                            @if($trailer)
                                <a href="{{ $trailer }}" target="_blank"
                                   class="rounded-2xl border border-red-400/30 px-4 py-2.5 text-sm font-semibold text-red-400 transition hover:bg-red-400/10">
                                    ▶ Trailer
                                </a>
                            @endif
                        </div>
                    </form>

                    @if($userAnime)
                        <form method="POST" action="{{ route('mylist.destroy', $userAnime) }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 transition hover:text-red-300">🗑️ Quitar de mi lista</button>
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
    </script>
</x-app-layout>