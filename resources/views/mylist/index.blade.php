<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mi Lista de Anime') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 px-4 py-3 rounded-lg bg-green-100 text-green-800 text-sm">{{ session('success') }}</div>
            @endif

            <div class="flex justify-end mb-4">
                <a href="{{ route('mylist.trash') }}"
                   class="text-sm text-gray-500 dark:text-gray-400 hover:underline">🗑️ Ver papelera</a>
            </div>

            {{-- 🎲 ¿Qué veo hoy? --}}
            @if($suggestion)
                <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex items-center gap-4">
                    <img src="{{ $suggestion->anime->image_url }}" class="w-12 h-16 object-cover rounded">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">🎲 ¿Qué veo hoy?</p>
                        <a href="{{ route('catalog.show', $suggestion->anime->mal_id) }}"
                           class="font-semibold text-gray-900 dark:text-white hover:underline">
                            {{ $suggestion->anime->title }}
                        </a>
                    </div>
                    <a href="{{ route('mylist.index') }}" title="Otra sugerencia" class="text-2xl hover:scale-125 transition">🎲</a>
                </div>
            @endif

            {{-- 🔍 Buscador en Mi Lista --}}
            <div class="mb-6">
                <input type="text" id="buscar-lista" placeholder="🔍 Buscar en mi lista..."
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            </div>

            {{-- 📥 Importar --}}
            <div class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">📥 Importar mi historial</h3>
                <form method="POST" action="{{ route('mylist.import') }}" class="mt-2 flex flex-wrap gap-2">
                    @csrf
                    <select name="source"
                            class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="anilist">AniList</option>
                        <option value="mal">MyAnimeList</option>
                    </select>
                    <input type="text" name="mal_username" required placeholder="Tu username de AniList o MAL"
                           class="flex-1 min-w-[200px] rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                        Importar
                    </button>
                </form>
            </div>
            {{-- 💾 Backup --}}
            <div class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex flex-wrap items-center gap-3">
                <h3 class="font-semibold text-gray-900 dark:text-white flex-1">💾 Backup de tu lista</h3>
                <a href="{{ route('mylist.export') }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold text-sm">
                    ⬇️ Descargar backup
                </a>
                <form method="POST" action="{{ route('mylist.importJson') }}"
                      enctype="multipart/form-data" class="flex flex-wrap gap-2 items-center">
                    @csrf
                    <input type="file" name="backup" accept=".json" required class="text-sm text-gray-500 dark:text-gray-400">
                    <button type="submit"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold text-sm">
                        ⬆️ Restaurar
                    </button>
                </form>
            </div>
            @forelse($items as $status => $group)
                <div class="mb-10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ \App\Models\UserAnime::STATUS_LABELS[$status] ?? $status }}
                        <span class="text-sm text-gray-500">({{ $group->count() }})</span>
                    </h3>

                    <div class="space-y-4">
                        @foreach($group as $item)
                            <div class="item-lista bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex gap-4"
                                 data-title="{{ mb_strtolower($item->anime->title) }}">
                                <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}"
                                     class="w-20 h-28 object-cover rounded-lg">

                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $item->anime->title }}
                                        @if($item->rewatch_count > 0)
                                            <span class="text-purple-500 text-sm">🔁 x{{ $item->rewatch_count + 1 }}</span>
                                        @endif
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        ⭐ {{ $item->score ?? '—' }}/10 · {{ $item->episodes_watched }} eps
                                        @if($item->anime->episodes_total)
                                            / {{ $item->anime->episodes_total }}
                                        @endif
                                    </p>

                                    @if($item->anime->episodes_total)
                                        <div class="mt-2 h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full rounded-full"
                                                 style="width: {{ $item->progressPercent() }}%; background: linear-gradient(90deg,#ec4899,#8b5cf6)"></div>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">{{ $item->progressPercent() }}%</p>
                                    @endif

                                    @if($item->notes)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 italic mt-2">📝 {{ $item->notes }}</p>
                                    @endif

                                    <div class="mt-3 flex flex-wrap items-center gap-3">
                                        <form method="POST" action="{{ route('mylist.update', $item) }}">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" onchange="this.form.submit()"
                                                    class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                @foreach(\App\Models\UserAnime::STATUS_LABELS as $value => $label)
                                                    <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>

                                        @if($item->status !== 'completed')
                                            <form method="POST" action="{{ route('mylist.increment', $item) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs px-3 py-1 rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200 font-semibold">
                                                    +1 episodio
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('mylist.rewatch', $item) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs px-3 py-1 rounded-lg bg-purple-100 text-purple-800 hover:bg-purple-200 font-semibold">
                                                    🔁 Lo volví a ver
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('mylist.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-500 hover:underline">Quitar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <p class="text-5xl mb-4">🎌</p>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Tu lista está vacía</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Explora el catálogo o importa tu historial de MAL.</p>
                    <a href="{{ route('catalog.index') }}"
                       class="inline-block mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                        Ir al catálogo
                    </a>
                </div>
            @endforelse

        </div>
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