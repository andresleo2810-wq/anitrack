@props(['percent' => 0])
<div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-800">
    <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-pink-500" style="width: {{ min(100, max(0, $percent)) }}%"></div>
</div>