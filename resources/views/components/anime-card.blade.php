@props(['malId', 'title', 'image', 'score' => null, 'type' => null, 'inList' => false])
<a href="{{ route('catalog.show', $malId) }}"
   class="group relative block overflow-hidden rounded-2xl border border-white/5 bg-[#111528] backdrop-blur transition-all duration-300 hover:border-pink-400/40 hover:shadow-xl hover:shadow-pink-500/20 hover:-translate-y-1">

    {{-- 🏷️ Badge tipo (TV/Película) arriba izquierda --}}
    @if($type)
        <span class="absolute left-3 top-3 z-20 rounded-md bg-black/70 px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-wider text-white backdrop-blur">
            {{ $type }}
        </span>
    @endif

    {{-- ✅ / ➕ Badge de estado en tu lista --}}
    @if($inList)
        <span title="En tu lista"
              class="absolute right-3 top-3 z-20 flex size-7 items-center justify-center rounded-full bg-emerald-400 text-[#070812] ring-2 ring-[#070812] shadow-lg shadow-emerald-400/50">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
            </svg>
        </span>
    @else
        <span title="Agregar a tu lista"
              class="absolute right-3 top-3 z-20 flex size-7 items-center justify-center rounded-full bg-gradient-to-br from-cyan-400 to-violet-500 text-white ring-2 ring-[#070812] shadow-lg shadow-cyan-400/40 transition-transform duration-300 group-hover:scale-110">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
        </span>
    @endif

    {{-- 🖼️ Imagen póster con overlay gradient al hover --}}
    <div class="relative aspect-[2/3] overflow-hidden bg-[#0b0d1c]">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
        @else
            <div class="flex h-full w-full items-center justify-center text-slate-700">
                <svg class="size-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
            </div>
        @endif
        {{-- Overlay gradient inferior para legibilidad --}}
        <div class="absolute inset-0 bg-gradient-to-t from-[#111528] via-transparent to-transparent opacity-80"></div>
    </div>

    {{-- 📝 Info --}}
    <div class="relative p-3">
        <h3 class="line-clamp-2 min-h-[2.5rem] text-sm font-bold leading-tight text-white">
            {{ $title }}
        </h3>
        <div class="mt-2 flex items-center justify-between">
            <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-amber-400">
                <svg class="size-3" fill="currentColor" viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6 6.2 8.7 12 3Z"/></svg>
                {{ $score ?? 'N/A' }}
            </span>
            @if($inList)
                <span class="rounded-full bg-cyan-500/15 px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-wider text-cyan-300">
                    En lista
                </span>
            @endif
        </div>
    </div>
</a>