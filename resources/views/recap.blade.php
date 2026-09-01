<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            🎁 Tu recap {{ $year }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($items->isEmpty())
                <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <p class="text-5xl mb-4">🎁</p>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Aún no hay datos de {{ $year }}</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Agrega anime este año para generar tu recap.</p>
                </div>
            @else
                {{-- HERO --}}
                <div class="rounded-2xl p-10 text-center text-white" style="background: linear-gradient(135deg,#ec4899,#8b5cf6,#3b82f6)">
                    <p class="text-6xl mb-4">🎌</p>
                    <h3 class="text-3xl font-extrabold">Tu año otaku {{ $year }}</h3>
                    <p class="mt-2 opacity-90">{{ $items->count() }} anime · {{ $eps }} episodios · {{ $horas }}h de pura animación</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Anime del año --}}
                    @if($top)
                        <div class="rounded-xl overflow-hidden shadow bg-white dark:bg-gray-800">
                            <img src="{{ $top->anime->image_url }}" class="w-full aspect-[2/3] object-cover" alt="{{ $top->anime->title }}">
                            <div class="p-4 text-center">
                                <p class="text-xs uppercase tracking-wide text-pink-500 font-bold">Tu anime del año</p>
                                <p class="font-semibold text-gray-900 dark:text-white mt-1">{{ $top->anime->title }}</p>
                                <p class="text-yellow-500 font-bold">★ {{ $top->score }}/10</p>
                            </div>
                        </div>
                    @endif

                    {{-- Género del año --}}
                    <div class="rounded-xl shadow bg-white dark:bg-gray-800 p-6 flex flex-col items-center justify-center text-center">
                        <p class="text-5xl mb-3">🎭</p>
                        <p class="text-xs uppercase tracking-wide text-purple-500 font-bold">Género del año</p>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-2">{{ $genero ?? '—' }}</p>
                    </div>

                    {{-- Mes más activo --}}
                    <div class="rounded-xl shadow bg-white dark:bg-gray-800 p-6 flex flex-col items-center justify-center text-center">
                        <p class="text-5xl mb-3">📆</p>
                        <p class="text-xs uppercase tracking-wide text-blue-500 font-bold">Mes más activo</p>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-2">{{ $mes }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $mesCount }} anime agregados</p>
                    </div>
                </div>

                {{-- Completados --}}
                <div class="rounded-xl shadow bg-white dark:bg-gray-800 p-6 text-center">
                    <p class="text-4xl font-extrabold text-green-500">{{ $completados }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">completados este año · promedio ★ {{ $avg }}</p>
                </div>
            @endif

            <div class="text-center">
                <a href="{{ route('dashboard') }}" class="text-indigo-600 dark:text-pink-500 hover:underline">← Volver al panel</a>
            </div>
        </div>
    </div>
</x-app-layout>