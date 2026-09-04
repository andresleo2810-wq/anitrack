@props(['malId', 'title', 'image', 'score' => null, 'type' => null])
<a href="{{ route('catalog.show', $malId) }}" class="group block rounded-2xl border border-white/10 bg-white/5 p-2 backdrop-blur transition hover:border-pink-500/40 hover:shadow-lg hover:shadow-pink-500/10">
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