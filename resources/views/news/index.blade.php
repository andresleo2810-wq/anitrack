<x-app-layout>
    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- HERO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="flex flex-col gap-3">
                <p class="font-mono text-[11px] font-bold uppercase tracking-[0.3em] text-cyan-400">Radar otaku</p>
                <h1 class="flex items-center gap-3 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Noticias
                    <span class="inline-block size-3 rounded-full bg-gradient-to-br from-pink-400 to-fuchsia-500 shadow-lg shadow-pink-500/60" aria-hidden="true"></span>
                </h1>
                <p class="max-w-lg text-sm text-slate-400">Lo último del mundo anime: episodios recién emitidos, promos y temporada actual.</p>
            </div>
        </section>

        {{-- 📺 Recién emitidos --}}
        @if(!empty($episodes))
            <section>
                <h2 class="mb-5 flex items-center gap-2.5 text-lg font-bold text-white">
                    <span class="h-5 w-1 rounded-full bg-gradient-to-b from-cyan-400 to-violet-500"></span>
                    Recién emitidos
                    <span class="ml-1 font-mono text-xs font-bold text-slate-500">({{ count($episodes) }})</span>
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 sm:gap-4">
                    @foreach($episodes as $e)
                        <a href="{{ route('catalog.show', $e['mal_id']) }}"
                           class="group overflow-hidden rounded-2xl border border-white/5 bg-[#111528]/80 backdrop-blur-xl transition hover:-translate-y-1 hover:border-pink-400/40 hover:shadow-xl hover:shadow-pink-500/15">
                            <div class="relative aspect-video overflow-hidden bg-[#0b0d1c]">
                                <img src="{{ $e['image_url'] ?? '' }}" alt="{{ $e['title'] ?? '' }}" class="size-full object-cover transition duration-500 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#111528] via-transparent to-transparent opacity-80"></div>
                                <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full border border-cyan-400/30 bg-black/70 px-2 py-0.5 font-mono text-[10px] font-bold text-cyan-300 backdrop-blur">
                                    <svg class="size-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                                    EP {{ $e['episode'] ?? '?' }}
                                </span>
                            </div>
                            <div class="p-3">
                                <p class="line-clamp-2 text-xs font-semibold text-slate-200">{{ $e['title'] ?? 'Sin título' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 🎬 Promos --}}
        @if(!empty($promos))
            <section>
                <h2 class="mb-5 flex items-center gap-2.5 text-lg font-bold text-white">
                    <span class="h-5 w-1 rounded-full bg-gradient-to-b from-pink-400 to-violet-500"></span>
                    Promos recientes
                    <span class="ml-1 font-mono text-xs font-bold text-slate-500">({{ count($promos) }})</span>
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($promos as $p)
                        @php
                            $img = $p['trailer']['images']['image_url'] ?? null;
                            $url = $p['trailer']['url'] ?? null;
                        @endphp
                        <div class="group overflow-hidden rounded-2xl border border-white/5 bg-[#111528]/80 backdrop-blur-xl transition hover:border-pink-400/40 hover:shadow-lg hover:shadow-pink-500/10">
                            <div class="relative aspect-video overflow-hidden bg-[#0b0d1c]">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $p['title'] ?? 'Promo' }}" class="size-full object-cover transition duration-500 group-hover:scale-110">
                                @else
                                    <div class="flex size-full items-center justify-center text-slate-700">
                                        <svg class="size-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-[#111528] via-transparent to-transparent"></div>
                                @if($url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener"
                                       class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition hover:opacity-100">
                                        <span class="flex size-14 items-center justify-center rounded-full bg-gradient-to-br from-pink-500 to-violet-600 shadow-lg shadow-pink-500/40 ring-4 ring-white/10">
                                            <svg class="size-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                                        </span>
                                    </a>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-2 p-3">
                                <p class="min-w-0 flex-1 truncate text-xs font-semibold text-slate-200">{{ $p['title'] ?? 'Promo' }}</p>
                                <a href="{{ route('catalog.show', $p['mal_id']) }}" class="shrink-0 inline-flex items-center gap-1 font-mono text-[10px] font-bold uppercase tracking-wider text-cyan-400 transition hover:text-cyan-300">
                                    Ficha
                                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 🌸 Temporada actual --}}
        @if(!empty($season))
            <section>
                <div class="mb-5 flex items-end justify-between gap-4">
                    <h2 class="flex items-center gap-2.5 text-lg font-bold text-white">
                        <span class="h-5 w-1 rounded-full bg-gradient-to-b from-fuchsia-400 to-pink-500"></span>
                        Sonando esta temporada
                        <span class="ml-1 font-mono text-xs font-bold text-slate-500">({{ count($season) }})</span>
                    </h2>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 sm:gap-4">
                    @foreach($season as $s)
                        <x-anime-card
                            :mal-id="$s['mal_id']"
                            :title="$s['title']"
                            :image="$s['images']['jpg']['image_url'] ?? null"
                            :score="$s['score'] ?? null"
                        />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 📡 Empty state --}}
        @if(empty($episodes) && empty($promos) && empty($season))
            <section class="relative overflow-hidden rounded-2xl border border-white/5 bg-[#111528]/80 p-12 text-center backdrop-blur-xl">
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-pink-500/10 blur-3xl"></div>
                <div class="relative">
                    <div class="mx-auto flex size-20 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10">
                        <svg class="size-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.348 3.061a45.751 45.751 0 0 1 5.302 0m7.36 1.455a8.39 8.39 0 0 1 1.94.144 2.225 2.225 0 0 1 1.86 2.21v11.26a2.25 2.25 0 0 1-1.5 2.122 8.017 8.017 0 0 0-2.15.687 2.25 2.25 0 0 1-2.938-.933 6.193 6.193 0 0 0-10.844 0 2.25 2.25 0 0 1-2.938.933A8.02 8.02 0 0 0 3.14 20.18a2.25 2.25 0 0 1-1.5-2.122V5.87a2.25 2.25 0 0 1 1.86-2.21 8.39 8.39 0 0 1 1.94-.144"/></svg>
                    </div>
                    <h3 class="mt-5 text-xl font-bold text-white">Radar sin señal</h3>
                    <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-slate-500">Las fuentes externas no responden ahora mismo. Intenta en unos minutos.</p>
                    <a href="{{ route('news.index', ['retry' => 1]) }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-pink-500 via-fuchsia-500 to-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-pink-500/30 transition hover:scale-[1.02]">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Reintentar radar
                    </a>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>