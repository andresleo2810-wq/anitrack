<x-app-layout>
    @php
        $pct = round($unlocked->count() / max(1, $achievements->count()) * 100);
        $tierAccent = [
            'gold' => 'from-amber-400 to-yellow-500',
            'silver' => 'from-slate-300 to-slate-500',
            'bronze' => 'from-orange-400 to-amber-600',
        ];
        $tierBadge = [
            'gold' => 'border-amber-400/30 bg-amber-400/10 text-amber-300',
            'silver' => 'border-slate-400/30 bg-slate-400/10 text-slate-300',
            'bronze' => 'border-orange-400/30 bg-orange-400/10 text-orange-300',
        ];
    @endphp

    <div class="mx-auto flex max-w-7xl flex-col gap-8">

        {{-- HERO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="flex flex-col gap-3">
                <p class="font-mono text-[11px] font-bold uppercase tracking-[0.3em] text-amber-400">Sala de trofeos</p>
                <h1 class="flex items-center gap-3 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Logros
                    <span class="inline-block size-3 rounded-full bg-gradient-to-br from-amber-400 to-pink-500 shadow-lg shadow-amber-500/60" aria-hidden="true"></span>
                </h1>
                <p class="text-sm text-slate-400">
                    {{ $unlocked->count() }} de {{ $achievements->count() }} desbloqueados ·
                    <span class="font-mono font-bold text-amber-400">{{ $totalPoints }} pts</span>
                </p>
            </div>

            {{-- Barra de progreso global --}}
            <div class="w-full rounded-2xl border border-white/5 bg-[#111528]/80 p-4 backdrop-blur-xl lg:w-96">
                <div class="mb-2 flex justify-between font-mono text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <span>Progreso</span>
                    <span class="text-amber-400">{{ $pct }}%</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-white/5">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 via-pink-500 to-fuchsia-500 shadow-lg shadow-amber-500/40 transition-all duration-700"
                         style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </section>

        {{-- GRID POR TIER --}}
        @foreach(['gold' => 'Oro', 'silver' => 'Plata', 'bronze' => 'Bronce'] as $tier => $tierLabel)
            @php $group = $achievements->where('tier', $tier); @endphp
            @if($group->isNotEmpty())
                <section>
                    <div class="mb-5 flex items-center gap-3">
                        <span class="h-5 w-1 rounded-full bg-gradient-to-b {{ $tierAccent[$tier] }}"></span>
                        <h2 class="text-lg font-bold text-white">{{ $tierLabel }}</h2>
                        <span class="rounded-full border {{ $tierBadge[$tier] }} px-2.5 py-0.5 font-mono text-[10px] font-bold">
                            {{ $group->where('id', '!=', 0)->filter(fn($a) => $unlocked->has($a->id))->count() }} / {{ $group->count() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($group as $a)
                            @php $u = $unlocked->get($a->id); @endphp
                            <div class="{{ $u
                                    ? 'border-amber-400/30 bg-gradient-to-br from-amber-400/10 via-[#111528] to-[#111528]'
                                    : 'border-white/5 bg-[#111528]/60 opacity-60'
                                }} relative overflow-hidden rounded-2xl border p-5 backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-lg {{ $u ? 'hover:shadow-amber-500/15' : 'hover:shadow-black/20' }}">
                                <div class="flex items-start gap-4">
                                    <div class="{{ $u ? '' : 'grayscale' }} flex size-14 shrink-0 items-center justify-center rounded-2xl border border-white/5 bg-[#0b0d1c] text-3xl {{ $a->tierColor() }}">
                                        {{ $a->icon }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h3 class="truncate font-bold text-white">{{ $a->name }}</h3>
                                            <span class="shrink-0 rounded-md bg-white/5 px-2 py-0.5 font-mono text-xs font-bold {{ $u ? 'text-amber-400' : 'text-slate-600' }}">+{{ $a->points }}</span>
                                        </div>
                                        <p class="mt-1 text-xs leading-relaxed text-slate-400">{{ $a->description }}</p>
                                        @if($u)
                                            <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 font-mono text-[10px] font-bold text-emerald-400">
                                                <svg class="size-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                                                {{ $u->unlocked_at->diffForHumans() }}
                                            </p>
                                        @else
                                            <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/5 px-2.5 py-1 font-mono text-[10px] font-bold text-slate-600">
                                                <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                                                Bloqueado
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @if($u)
                                    <div class="pointer-events-none absolute -right-6 -top-6 size-24 rounded-full bg-amber-400/15 blur-2xl"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</x-app-layout>