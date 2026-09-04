@props(['malId', 'title', 'image', 'score' => null, 'type' => null, 'inList' => false])
<a href="{{ route('catalog.show', $malId) }}" class="group relative block rounded-2xl border border-white/10 bg-white/5 p-2 backdrop-blur transition hover:border-pink-500/40 hover:shadow-lg hover:shadow-pink-500/10">

    @if($inList)
        <span title="En tu lista" class="absolute right-4 top-4 z-20 flex size-7 items-center justify-center rounded-full bg-emerald-400 text-[#080d1a] ring-2 ring-[#080d1a] shadow-lg shadow-emerald-400/50">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
            </svg>
        </span>
    @else
        <span title="No está en tu lista" class="absolute right-4 top-4 z-20 flex size-7 items-center justify-center rounded-full bg-gradient-to-br from-cyan-400 to-violet-500 text-white ring-2 ring-[#080d1a] shadow-lg shadow-cyan-400/40">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
        </span>
    @endif

    <div class="aspect-[2/3] overflow-hidden rounded-xl bg-slate-800">
        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
    </div>
    <div class="px-1 pb-1 pt-3">
        <h3 class="truncate text-sm font-semibold text-slate-100">{{ $title }}</h3>
        <div class="mt-1 flex items-center justify-between font-mono text-xs">
            <span class="text-amber-400">★ {{ $score ?? 'N/A' }}</span>
            <span class="text-slate-500">{{ $type ?? 'TV' }}</span>
        </div>
    </div>
</a>