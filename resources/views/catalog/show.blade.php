<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $anime['title_spanish'] ?? $anime['title'] }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <a href="{{ route('catalog.index') }}"
               class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">← Volver al catálogo</a>

            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mt-4 px-4 py-3 rounded-lg bg-green-100 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Errores de validación (ahora visibles) --}}
            @if($errors->any())
                <div class="mt-4 px-4 py-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-4 bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <div class="md:flex">

                    {{-- Poster --}}
                    <div class="md:w-72 shrink-0 bg-gray-200 dark:bg-gray-700">
                        <img src="{{ $anime['images']['jpg']['image_url'] }}"
                             alt="{{ $anime['title'] }}"
                             class="w-full h-full object-cover">
                    </div>

                    {{-- Información --}}
                    <div class="p-6 flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $anime['title_spanish'] ?? $anime['title'] }}
                        </h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">
                            {{ !empty($anime['title_spanish']) ? $anime['title'] : ($anime['title_english'] ?? '') }}
                        </p>

                        {{-- Badges --}}
                        <div class="mt-4 flex flex-wrap gap-2 text-sm">
                            @if(!empty($anime['score']))
                                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold">★ {{ $anime['score'] }}</span>
                            @endif
                            @if(!empty($anime['type']))
                                <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-800">{{ $anime['type'] }}</span>
                            @endif
                            @if(!empty($anime['episodes']))
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-800">{{ $anime['episodes'] }} episodios</span>
                            @endif
                            @if(!empty($anime['status']))
                                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ $anime['status'] }}</span>
                            @endif
                        </div>
                        {{-- ⚖️ Tu score vs comunidad --}}
                        @if($userAnime && $userAnime->score && !empty($anime['score']))
                            <div class="mt-4 bg-gray-100 dark:bg-gray-700/50 rounded-lg p-3">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-2">⚖️ Tu opinión vs la comunidad</p>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-gray-600 dark:text-gray-300">Comunidad</span>
                                            <span class="font-semibold text-yellow-500">★ {{ $anime['score'] }}</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-gray-300 dark:bg-gray-600 overflow-hidden">
                                            <div class="h-full bg-yellow-400" style="width: {{ min(100, $anime['score'] * 10) }}%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-gray-600 dark:text-gray-300">Tú</span>
                                            <span class="font-semibold text-pink-500">★ {{ $userAnime->score }}</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-gray-300 dark:bg-gray-600 overflow-hidden">
                                            <div class="h-full" style="width: {{ $userAnime->score * 10 }}%; background: linear-gradient(90deg,#ec4899,#8b5cf6)"></div>
                                        </div>
                                    </div>
                                </div>
                                @php $diff = round($userAnime->score - $anime['score'], 1); @endphp
                                <p class="text-xs mt-2 text-gray-500 dark:text-gray-400">
                                    @if($diff > 0.5)
                                        💖 Te gustó {{ $diff }} puntos MÁS que a la comunidad
                                    @elseif($diff < -0.5)
                                        🤷 Te gustó {{ abs($diff) }} puntos MENOS que a la comunidad
                                    @else
                                        🤝 Opinión muy similar a la comunidad
                                    @endif
                                </p>
                            </div>
                        @endif
                        {{-- Géneros --}}
                        @if(!empty($anime['genres']))
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($anime['genres'] as $genre)
                                    @php $name = is_array($genre) ? ($genre['name'] ?? '') : $genre; @endphp
                                    @if($name)
                                        <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">{{ $name }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                                                {{-- Sinopsis con anti-spoiler --}}
                        @if(!empty($anime['synopsis']))
                            <div class="mt-6" id="sinopsis-box"
                                 data-locked="{{ (!$userAnime || $userAnime->status !== 'completed') ? '1' : '0' }}">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sinopsis</h3>
                                <p id="sinopsis-text" class="mt-2 text-gray-600 dark:text-gray-300 leading-relaxed">{{ $anime['synopsis'] }}</p>
                                <button id="btn-spoiler" type="button"
                                        class="mt-2 text-xs px-3 py-1 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 hidden">
                                    👁️ Revelar sinopsis
                                </button>
                            </div>
                        @endif

                        {{-- Formulario Mi Lista --}}
                        <form method="POST" action="{{ route('mylist.store') }}" class="mt-6">
                            @csrf
                            <input type="hidden" name="mal_id" value="{{ $anime['mal_id'] }}">
                            <input type="hidden" name="title" value="{{ $anime['title'] }}">
                            <input type="hidden" name="image_url" value="{{ $anime['images']['jpg']['image_url'] ?? null }}">
                            <input type="hidden" name="score_api" value="{{ $anime['score'] ?? null }}">
                            <input type="hidden" name="episodes_api" value="{{ $anime['episodes'] ?? null }}">
                            <input type="hidden" name="genres" value="{{ json_encode(collect($anime['genres'] ?? [])->map(fn($g) => is_array($g) ? ($g['name'] ?? '') : $g)->values()) }}">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                                    <select name="status" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                        @foreach(\App\Models\UserAnime::STATUS_LABELS as $value => $label)
                                            <option value="{{ $value }}" {{ ($userAnime->status ?? 'plan_to_watch') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tu puntuación (1-10)</label>
                                    <input type="number" name="score" min="1" max="10"
                                           value="{{ $userAnime->score ?? '' }}"
                                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Episodios vistos</label>
                                    <input type="number" name="episodes_watched" min="0"
                                           value="{{ $userAnime->episodes_watched ?? 0 }}"
                                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                            </div>
                                                        {{-- 📝 Notas personales --}}
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">📝 Notas personales</label>
                                <textarea name="notes" rows="2"
                                          placeholder="Ej: ver manga después, el OST es increíble..."
                                          class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ $userAnime->notes ?? '' }}</textarea>
                            </div>

                            {{-- 📅 Fechas --}}
                            <div class="grid grid-cols-2 gap-3 mt-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">📅 Empezado</label>
                                    <input type="date" name="started_at"
                                           value="{{ $userAnime?->started_at?->toDateString() }}"
                                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">🏁 Terminado</label>
                                    <input type="date" name="finished_at"
                                           value="{{ $userAnime?->finished_at?->toDateString() }}"
                                           class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-3">
                                <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                                    {{ $userAnime ? '💾 Actualizar mi lista' : '+ Agregar a mi lista' }}
                                </button>

                                @php $trailer = $anime['trailer_url'] ?? ($anime['trailer']['url'] ?? null); @endphp
                                @if($trailer)
                                    <a href="{{ $trailer }}" target="_blank"
                                       class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">▶ Ver trailer</a>
                                @endif
                            </div>
                        </form>

                        @if($userAnime)
                            <form method="POST" action="{{ route('mylist.destroy', $userAnime) }}" class="mt-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:underline">🗑️ Quitar de mi lista</button>
                            </form>
                        @endif
                    </div>
                </div>
                        </div>

            {{-- ✨ Similares --}}
            @if(!empty($similar))
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">✨ Si te gustó, prueba con...</h3>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-4">
                        @foreach($similar as $rec)
                            <a href="{{ route('catalog.show', $rec['mal_id']) }}"
                               class="group block bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                                <div class="aspect-[2/3] overflow-hidden bg-gray-200 dark:bg-gray-700">
                                    <img src="{{ $rec['image'] }}" alt="{{ $rec['title'] }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <p class="p-2 text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $rec['title'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

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