@props(['title', 'subtitle' => null])
<div class="mb-4 flex items-end justify-between gap-4">
    <div>
        <h2 class="font-mono text-lg font-bold text-slate-50">{{ $title }}</h2>
        @if($subtitle)<p class="text-xs text-slate-500">{{ $subtitle }}</p>@endif
    </div>
    {{ $action ?? '' }}
</div>