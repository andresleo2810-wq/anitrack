<x-app-layout>
    <div class="mx-auto flex max-w-5xl flex-col gap-8">

        {{-- HERO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="flex flex-col gap-3">
                <p class="font-mono text-[11px] font-bold uppercase tracking-[0.3em] text-fuchsia-400">Resumen anual</p>
                <h1 class="flex items-center gap-3 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Tu recap {{ $year }}
                    <span class="inline-block size-3 rounded-full bg-gradient-to-br from-fuchsia-400 to-cyan-400 shadow-lg shadow-fuchsia-500/60" aria-hidden="true"></span>
                </h1>
                <p class="max-w-lg text-sm text-slate-400">Tu año otaku en números: lo que viste, lo que amaste y lo que no soltaste.</p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 font-mono text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500 transition hover:text-cyan-400">
                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6 6-6-6 6-6"/></svg>
                Volver al panel
            </a>
        </section>

        @if($items->isEmpty())
            <section class="relative overflow-hidden rounded-2xl border border-white/5 bg-[#111528]/80 p-16 text-center backdrop-blur-xl">
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-fuchsia-500/10 blur-3xl"></div>
                <div class="relative">
                    <div class="mx-auto flex size-20 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10">
                        <svg class="size-10 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v7.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75v-7.5m18 0A2.25 2.25 0 0 0 18.75 9H5.25A2.25 2.25 0 0 0 3 11.25m18 0V7.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 6.75 3 7.254 3 7.875V11.25m9-4.5v14.25m0-14.25a2.25 2.25 0 0 0-2.25-2.25h-1.5A2.25 2.25 0 0 0 6 6.75m6 0a2.25 2.25 0 0 1 2.25-2.25h1.5A2.25 2.25 0 0 1 18 6.75"/></svg>
                    </div>
                    <h3 class="mt-5 text-xl font-bold text-white">Aún no hay datos de {{ $year }}</h3>
                    <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-slate-500">Agrega anime este año para generar tu recap.</p>
                    <a href="{{ route('catalog.index') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-pink-500 via-fuchsia-500 to-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-pink-500/30 transition hover:scale-[1.02]">
                        Explorar catálogo
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </a>
                </div>
            </section>
        @else
            {{-- 🎌 HERO DEL AÑO --}}
            <section class="relative overflow-hidden rounded-2xl border border-white/10 p-8 text-center sm:p-12"
                     style="background: linear-gradient(135deg, rgba(236,72,153,.25), rgba(139,92,246,.25), rgba(34,211,238,.15)), #111528">
                <div aria-hidden="true" class="pointer-events-none absolute -left-16 -top-16 size-56 rounded-full bg-pink-500/20 blur-3xl"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -bottom-16 -right-16 size-56 rounded-full bg-cyan-500/15 blur-3xl"></div>
                <div class="relative">
                    <p class="font-mono text-[11px] font-bold uppercase tracking-[0.3em] text-cyan-300">Tu año otaku</p>
                    <h2 class="mt-3 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $year }}</h2>
                    <div class="mx-auto mt-6 flex max-w-md flex-wrap items-center justify-center gap-x-6 gap-y-2 font-mono text-sm text-slate-300">
                        <span><span class="font-bold text-white">{{ $items->count() }}</span> anime</span>
                        <span class="text-slate-600">·</span>
                        <span><span class="font-bold text-white">{{ $eps }}</span> episodios</span>
                        <span class="text-slate-600">·</span>
                        <span><span class="font-bold text-white">{{ $horas }}h</span> de animación</span>
                    </div>
                </div>
            </section>

            {{-- 🏆 DESTACADOS --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                {{-- Anime del año --}}
                @if($top)
                    <article class="group overflow-hidden rounded-2xl border border-white/5 bg-[#111528]/80 backdrop-blur-xl transition hover:-translate-y-1 hover:border-pink-400/40 hover:shadow-xl hover:shadow-pink-500/15">
                        <div class="relative overflow-hidden">
                            <img src="{{ $top->anime->image_url }}" alt="{{ $top->anime->title }}" class="aspect-[2/3] w-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#111528] via-transparent to-transparent"></div>
                            <span class="absolute left-3 top-3 rounded-full border border-pink-400/30 bg-black/70 px-2.5 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-pink-300 backdrop-blur">Tu anime del año</span>
                        </div>
                        <div class="p-4 text-center">
                            <p class="truncate text-sm font-bold text-white">{{ $top->anime->title }}</p>
                            <p class="mt-1 font-mono text-xs font-bold text-amber-400">★ {{ $top->score }}/10</p>
                        </div>
                    </article>
                @endif

                {{-- Género del año --}}
                <article class="flex flex-col items-center justify-center rounded-2xl border border-white/5 bg-[#111528]/80 p-8 text-center backdrop-blur-xl">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-violet-500/15 text-violet-300 ring-1 ring-violet-400/20">
                        <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4.97 0-9 3.582-9 8 0 1.82.634 3.51 1.714 4.904-.203 1.09-.71 2.258-1.464 3.346 1.69-.13 3.26-.723 4.56-1.627C9.09 18.44 10.5 18.75 12 18.75c4.97 0 9-3.582 9-8s-4.03-7.75-9-7.75Z"/></svg>
                    </span>
                    <p class="mt-4 font-mono text-[10px] font-bold uppercase tracking-[0.22em] text-violet-400">Género del año</p>
                    <p class="mt-2 text-2xl font-extrabold text-white">{{ $genero ?? '—' }}</p>
                </article>

                {{-- Mes más activo --}}
                <article class="flex flex-col items-center justify-center rounded-2xl border border-white/5 bg-[#111528]/80 p-8 text-center backdrop-blur-xl">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-cyan-500/15 text-cyan-300 ring-1 ring-cyan-400/20">
                        <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 7.5h16.5M4.5 5.25h15a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5h-15a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
                    </span>
                    <p class="mt-4 font-mono text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-400">Mes más activo</p>
                    <p class="mt-2 text-2xl font-extrabold text-white">{{ $mes }}</p>
                    <p class="mt-1 font-mono text-[11px] text-slate-500">{{ $mesCount }} anime agregados</p>
                </article>
            </div>

            {{-- ✅ Completados --}}
            <section class="relative overflow-hidden rounded-2xl border border-white/5 bg-[#111528]/80 p-8 text-center backdrop-blur-xl">
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-emerald-500/10 blur-3xl"></div>
                <div class="relative">
                    <p class="font-mono text-5xl font-extrabold text-emerald-400">{{ $completados }}</p>
                    <p class="mt-2 font-mono text-[11px] uppercase tracking-[0.22em] text-slate-500">
                        completados este año · promedio <span class="font-bold text-amber-400">★ {{ $avg }}</span>
                    </p>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>