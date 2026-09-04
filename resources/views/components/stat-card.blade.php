@props(['icon' => 'star', 'value' => '0', 'label' => '', 'color' => 'cyan'])
@php
$map = [
    'cyan' => 'bg-cyan-400/10 text-cyan-400 shadow-cyan-400/20',
    'pink' => 'bg-pink-500/10 text-pink-400 shadow-pink-500/20',
    'violet' => 'bg-violet-500/10 text-violet-400 shadow-violet-500/20',
    'emerald' => 'bg-emerald-400/10 text-emerald-400 shadow-emerald-400/20',
    'amber' => 'bg-amber-400/10 text-amber-400 shadow-amber-400/20',
];
$c = $map[$color] ?? $map['cyan'];
@endphp
<div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur transition hover:border-cyan-400/30">
    <span class="flex size-10 items-center justify-center rounded-full {{ $c }} shadow-lg">
        <i data-lucide="{{ $icon }}" class="size-5"></i>
    </span>
    <p class="mt-4 font-mono text-3xl font-bold text-slate-50">{{ $value }}</p>
    <p class="mt-1 text-xs text-slate-400">{{ $label }}</p>
</div>