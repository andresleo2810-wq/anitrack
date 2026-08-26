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

            @if($items->isEmpty())
                <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <p class="text-5xl mb-4">🎌</p>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Tu lista está vacía</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Explora el catálogo y agrega tu primer anime.</p>
                    <a href="{{ route('catalog.index') }}"
                       class="inline-block mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                        Ir al catálogo
                    </a>
                </div>
            @else
                @foreach(\App\Models\UserAnime::STATUS_LABELS as $status => $label)
                    @if($items->has($status))
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-10 mb-4">
                            {{ $label }}
                            <span class="text-sm font-normal text-gray-500">({{ $items[$status]->count() }})</span>
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($items[$status] as $item)
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex gap-4">
                                    <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}"
                                         class="w-20 h-28 object-cover rounded">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $item->anime->title }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            @if($item->score) ⭐ {{ $item->score }}/10 @endif
                                            · {{ $item->episodes_watched }} eps
                                        </p>

                                        <form method="POST" action="{{ route('mylist.update', $item) }}" class="mt-3">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" onchange="this.form.submit()"
                                                    class="text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                                @foreach(\App\Models\UserAnime::STATUS_LABELS as $value => $text)
                                                    <option value="{{ $value }}" {{ $item->status === $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        </form>

                                        <form method="POST" action="{{ route('mylist.destroy', $item) }}" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:underline">Quitar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @endif

        </div>
    </div>
</x-app-layout>