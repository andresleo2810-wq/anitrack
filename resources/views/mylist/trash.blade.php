<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('🗑️ Papelera') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 px-4 py-3 rounded-lg bg-green-100 text-green-800 text-sm">{{ session('success') }}</div>
            @endif

            <a href="{{ route('mylist.index') }}"
               class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">← Volver a Mi Lista</a>

            @forelse($items as $item)
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex gap-4 items-center">
                    <img src="{{ $item->anime->image_url }}" alt="{{ $item->anime->title }}"
                         class="w-14 h-20 object-cover rounded-lg opacity-70">

                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ $item->anime->title }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Eliminado el {{ $item->deleted_at->format('d/m/Y') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('mylist.restore', $item->id) }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1 rounded-lg bg-green-100 text-green-800 hover:bg-green-200 text-sm font-semibold">
                            ♻️ Restaurar
                        </button>
                    </form>

                    <form method="POST" action="{{ route('mylist.force', $item->id) }}"
                          onsubmit="return confirm('¿Eliminar para siempre? No se podrá recuperar.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1 rounded-lg bg-red-100 text-red-800 hover:bg-red-200 text-sm font-semibold">
                            🔥 Para siempre
                        </button>
                    </form>
                </div>
            @empty
                <div class="mt-6 text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <p class="text-5xl mb-4">🗑️</p>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Papelera vacía</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Los anime que quites de tu lista aparecerán aquí.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>