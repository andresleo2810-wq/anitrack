<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($stats['total'] === 0)
                <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <p class="text-5xl mb-4">📊</p>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Aún no hay estadísticas</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Agrega anime a tu lista para ver tu panel otaku.</p>
                    <a href="{{ route('catalog.index') }}"
                       class="inline-block mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                        Ir al catálogo
                    </a>
                </div>
            @else
                {{-- Tarjetas --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                        <p class="text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">En lista</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                        <p class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Completados</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['watching'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Viendo</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                        <p class="text-3xl font-bold text-purple-600">{{ $stats['episodes'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Episodios</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                        @php
                            $h = intdiv($stats['minutes'], 60);
                            $tiempo = $h >= 24 ? intdiv($h, 24) . 'd ' . ($h % 24) . 'h' : $h . 'h';
                        @endphp
                        <p class="text-3xl font-bold text-pink-500">{{ $tiempo }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tiempo invertido</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                        <p class="text-3xl font-bold text-yellow-500">★ {{ $stats['avg_score'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Promedio</p>
                    </div>
                </div>

                {{-- 🎁 Botón al Recap anual --}}
                <div class="mt-4 text-right">
                    <a href="{{ route('recap') }}"
                       class="inline-block px-4 py-2 rounded-lg text-white font-semibold shadow hover:opacity-90 transition"
                       style="background: linear-gradient(135deg,#ec4899,#8b5cf6)">
                        🎁 Tu recap {{ now()->year }}
                    </a>
                </div>

                {{-- 🔔 Episodios nuevos --}}
                @if(isset($avisos) && $avisos->isNotEmpty())
                    <div class="mt-8 bg-gradient-to-r from-pink-600/20 to-purple-600/20 border border-pink-500/40 rounded-xl p-4">
                        <h3 class="text-lg font-semibold text-pink-500 mb-3">🔔 Tienes episodios nuevos por ver</h3>
                        <div class="space-y-2">
                            @foreach($avisos as $av)
                                <a href="{{ route('catalog.show', $av['mal_id']) }}" class="flex items-center gap-3 group">
                                    <img src="{{ $av['image'] }}" class="w-8 h-12 object-cover rounded" alt="{{ $av['title'] }}">
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        <span class="font-semibold group-hover:underline">{{ $av['title'] }}</span>
                                        — {{ $av['texto'] }} 🎉
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 📺 Continuar viendo --}}
                @if($watching->isNotEmpty())
                    <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📺 Continuar viendo</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($watching as $item)
                                <div class="rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                    <a href="{{ route('catalog.show', $item->anime->mal_id) }}">
                                        <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}"
                                             class="w-full aspect-[2/3] object-cover">
                                    </a>
                                    <div class="p-3">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $item->anime->title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Ep {{ $item->episodes_watched }}@if($item->anime->episodes_total) / {{ $item->anime->episodes_total }}@endif
                                        </p>
                                        @if($item->anime->episodes_total)
                                            <div class="mt-1 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600 overflow-hidden">
                                                <div class="h-full" style="width: {{ $item->progressPercent() }}%; background: linear-gradient(90deg,#ec4899,#8b5cf6)"></div>
                                            </div>
                                        @endif
                                        <form method="POST" action="{{ route('mylist.increment', $item) }}" class="mt-2">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full text-xs px-2 py-1 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700">
                                                +1 episodio
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Gráficas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Anime por estado</h3>
                        <canvas id="statusChart" height="220"></canvas>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Tus géneros favoritos</h3>
                        <canvas id="genreChart" height="220"></canvas>
                    </div>
                </div>

                {{-- Top puntuados --}}
                <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Tus favoritos (mejor puntuados)</h3>
                    <div class="space-y-3">
                        @foreach($topRated as $item)
                            <div class="flex items-center gap-4">
                                <img src="{{ $item->anime->image_url }}" class="w-10 h-14 object-cover rounded">
                                <span class="flex-1 text-gray-900 dark:text-gray-200">{{ $item->anime->title }}</span>
                                <span class="text-yellow-500 font-semibold">★ {{ $item->score }}/10</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 📅 Calendario semanal --}}
                @if(!empty($schedule) && count(array_filter($schedule)) > 0)
                    <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📅 Esta semana en emisión</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                            @foreach($schedule as $dia => $animes)
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                    <h4 class="font-semibold text-sm text-indigo-600 dark:text-pink-500 mb-2">{{ $dia }}</h4>
                                    <div class="space-y-2">
                                        @forelse($animes as $a)
                                            <a href="{{ route('catalog.show', $a['mal_id']) }}"
                                               class="flex items-center gap-2 group">
                                                <img src="{{ $a['image_url'] }}"
                                                     class="w-8 h-12 object-cover rounded"
                                                     alt="{{ $a['title'] }}">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium text-gray-900 dark:text-white truncate group-hover:underline">
                                                        {{ $a['title'] }}
                                                    </p>
                                                    @if($a['score'])
                                                        <p class="text-xs text-yellow-500">★ {{ $a['score'] }}</p>
                                                    @endif
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-xs text-gray-400 italic">Sin estrenos</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 🤖 Recomendaciones IA --}}
                @if(isset($recommendations) && $recommendations->isNotEmpty())
                    <div class="mt-8">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">🤖 Recomendados para ti</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Porque te gusta: {{ isset($topGenres) ? $topGenres->implode(' · ') : 'tus géneros favoritos' }}
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            @foreach($recommendations as $rec)
                                <a href="{{ route('catalog.show', $rec['mal_id']) }}"
                                   class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                                    <img src="{{ $rec['images']['jpg']['image_url'] }}"
                                         alt="{{ $rec['title'] }}"
                                         class="w-full aspect-[2/3] object-cover">
                                    <div class="p-2">
                                        <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $rec['title'] }}</p>
                                        <p class="text-xs text-yellow-500 font-semibold">★ {{ $rec['score'] ?? 'N/A' }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

        </div>
    </div>

    @if($stats['total'] > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: @json($byStatus->keys()->map(fn($s) => \App\Models\UserAnime::STATUS_LABELS[$s] ?? $s)->values()),
                datasets: [{
                    data: @json($byStatus->values()),
                    backgroundColor: @json($byStatus->keys()->map(fn($s) => $statusColors[$s] ?? '#94a3b8')->values())
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('genreChart'), {
            type: 'bar',
            data: {
                labels: @json($byGenre->keys()->values()),
                datasets: [{
                    data: @json($byGenre->values()->values()),
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { stepSize: 1 } } } }
        });
    </script>
    @endif
</x-app-layout>