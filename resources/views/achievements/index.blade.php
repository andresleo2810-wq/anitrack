<x-app-layout>
    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- HERO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-400">Sala de trofeos</p>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Logros <span class="text-amber-400">🏆</span>
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $unlocked->count() }} de {{ $achievements->count() }} desbloqueados ·
                    <span class="font-mono font-bold text-amber-400">{{ $totalPoints }} pts</span>
                </p>
            </div>

            {{-- Barra de progreso global --}}
            <div class="w-full lg:w-80">
                <div class="mb-1 flex justify-between font-mono text-xs text-slate-400">
                    <span>Progreso</span>
                    <span>{{ round($unlocked->count() / max(1, $achievements->count()) * 100) }}%</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-pink-500 transition-all"
                         style="width: {{ round($unlocked->count() / max(1, $achievements->count()) * 100) }}%"></div>
                </div>
            </div>
        </section>

        {{-- GRID POR TIER --}}
        @foreach(['gold' => '🥇 Oro', 'silver' => '🥈 Plata', 'bronze' => '🥉 Bronce'] as $tier => $tierLabel)
            @php $group = $achievements->where('tier', $tier); @endphp
            @if($group->isNotEmpty())
                <section>
                    <h2 class="mb-4 text-lg font-bold text-white">{{ $tierLabel }}</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($group as $a)
                            @php $u = $unlocked->get($a->id); @endphp
                            <div class="{{ $u ? 'border-amber-400/30 bg-amber-400/5' : 'border-slate-700/80 bg-slate-900/60 opacity-60' }} relative overflow-hidden rounded-3xl border p-5 backdrop-blur-xl transition hover:scale-[1.01]">
                                <div class="flex items-start gap-4">
                                    <div class="{{ $u ? '' : 'grayscale' }} flex size-14 shrink-0 items-center justify-center rounded-2xl text-3xl {{ $a->tierColor() }}">
                                        {{ $a->icon }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h3 class="truncate font-bold text-white">{{ $a->name }}</h3>
                                            <span class="font-mono text-xs font-bold {{ $u ? 'text-amber-400' : 'text-slate-600' }}">+{{ $a->points }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-400">{{ $a->description }}</p>
                                        @if($u)
                                            <p class="mt-2 font-mono text-[10px] text-emerald-400">
                                                ✓ Desbloqueado {{ $u->unlocked_at->diffForHumans() }}
                                            </p>
                                        @else
                                            <p class="mt-2 font-mono text-[10px] text-slate-600">🔒 Bloqueado</p>
                                        @endif
                                    </div>
                                </div>
                                @if($u)
                                    <div class="pointer-events-none absolute -right-6 -top-6 size-20 rounded-full bg-amber-400/10 blur-2xl"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</x-app-layout>