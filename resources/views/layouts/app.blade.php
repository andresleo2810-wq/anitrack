<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('logo-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#070812">
    <title>AniTrack</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* 📱 Safe area iOS (notch + home indicator) */
        body { padding-bottom: env(safe-area-inset-bottom); }
        @media (max-width: 1023px) {
            main { padding-bottom: calc(5rem + env(safe-area-inset-bottom)) !important; }
        }
    </style>
</head>

@php
    $user = auth()->user();
    $partes = collect(explode(' ', $user->name))->filter()->take(2);
    $iniciales = strtoupper($partes->map(fn($p) => $p[0])->implode(''));
    $totalLista = \App\Models\UserAnime::where('user_id', $user->id)->count();

    $fechas = \App\Models\UserAnime::where('user_id', $user->id)
        ->pluck('updated_at')->map(fn($f) => $f->toDateString())->unique()->flip();
    $dia = now()->startOfDay();
    if (!$fechas->has($dia->toDateString())) $dia->subDay();
    $racha = 0;
    while ($fechas->has($dia->toDateString())) { $racha++; $dia->subDay(); }
@endphp

<body class="min-h-screen min-h-[100svh] bg-[#070812] text-slate-200 antialiased">
    {{-- Fondo manga + capa degradada --}}
    <div class="fixed inset-0 -z-10 bg-[url('/anitrack-manga-bg.png')] bg-cover bg-center opacity-20"></div>
    <div class="fixed inset-0 -z-10" style="background: radial-gradient(ellipse at top, rgba(139,92,246,.08), transparent 60%), linear-gradient(to bottom, rgba(7,8,18,.55), rgba(7,8,18,.85), rgba(7,8,18,.98))"></div>

    {{-- Halos de luz premium --}}
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-32 -top-32 size-[28rem] rounded-full bg-fuchsia-600/10 blur-[120px]"></div>
        <div class="absolute right-0 top-1/4 size-[26rem] rounded-full bg-cyan-500/5 blur-[110px]"></div>
    </div>

    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/60 backdrop-blur-sm lg:hidden"></div>

    <div class="flex min-h-screen min-h-[100svh]">
        {{-- ===== SIDEBAR DESKTOP ===== --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 shrink-0 -translate-x-full overflow-y-auto border-r border-white/5 bg-[#0b1120]/85 backdrop-blur-xl transition-transform lg:static lg:translate-x-0">
            {{-- Logo --}}
            <div class="flex h-16 items-center border-b border-white/5 px-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                                        <img src="{{ asset('logo-icon.png') }}" alt="AniTrack" class="size-9 rounded-xl shadow-lg shadow-pink-500/30">
                    <span class="font-mono text-xl font-bold tracking-tight text-white">Ani<span class="text-pink-400">Track</span></span>
                </a>
                <button type="button" id="btn-close-sidebar" class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden">
                    <i data-lucide="x" class="size-5"></i>
                </button>
            </div>

            <nav class="flex flex-col gap-6 px-4 py-6">
                {{-- Menú principal --}}
                <div>
                    <p class="mb-3 px-3 font-mono text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500">Menú principal</p>
                    <div class="flex flex-col gap-1">
                        @php
                            $nav = [
                                ['route' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
                                ['route' => 'catalog.*', 'icon' => 'library', 'label' => 'Catálogo'],
                                ['route' => 'news.*', 'icon' => 'newspaper', 'label' => 'Noticias'],
                                ['route' => 'mylist.*', 'icon' => 'list-video', 'label' => 'Mi lista', 'badge' => $totalLista],
                                ['route' => 'achievements.*', 'icon' => 'trophy', 'label' => 'Logros'],
                            ];
                        @endphp
                        @foreach($nav as $item)
                            @php
                                $active = request()->routeIs($item['route']);
                                $activeClass = $active
                                    ? 'bg-gradient-to-r from-pink-500/15 via-fuchsia-500/10 to-violet-500/5 text-white border-l-2 border-pink-400 rounded-r-2xl rounded-l-md shadow-[inset_0_0_12px_rgba(236,72,153,.08)]'
                                    : 'text-slate-400 hover:bg-white/5 hover:text-white border-l-2 border-transparent rounded-2xl';
                            @endphp
                            <a href="{{ $active ? '#' : (str_contains($item['route'], '*') ? route(str_replace('.*', '.index', $item['route'])) : route($item['route'])) }}"
                               class="{{ $activeClass }} flex items-center justify-between px-3 py-2.5 text-sm font-medium transition focus-visible:ring-2 focus-visible:ring-cyan-400">
                                <span class="flex items-center gap-3">
                                    <i data-lucide="{{ $item['icon'] }}" class="size-4 {{ $active ? 'text-pink-300' : '' }}"></i>
                                    {{ $item['label'] }}
                                </span>
                                @if(isset($item['badge']))
                                    <span class="rounded-full bg-white/10 px-2 py-0.5 font-mono text-[10px] {{ $active ? 'text-pink-200' : 'text-slate-400' }}">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Tu espacio --}}
                <div>
                    <p class="mb-3 px-3 font-mono text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500">Tu espacio</p>
                    <div class="flex flex-col gap-1">
                        @php
                            $activeRecap = request()->routeIs('recap');
                            $activeProfile = request()->routeIs('profile.*');
                        @endphp
                        <a href="{{ route('recap') }}"
                           class="{{ $activeRecap ? 'bg-gradient-to-r from-violet-500/15 to-fuchsia-500/5 text-white border-l-2 border-violet-400 rounded-r-2xl rounded-l-md' : 'text-slate-400 hover:bg-white/5 hover:text-white border-l-2 border-transparent rounded-2xl' }} flex items-center justify-between px-3 py-2.5 text-sm font-medium transition">
                            <span class="flex items-center gap-3">
                                <i data-lucide="sparkles" class="size-4"></i>
                                Recap {{ now()->year }}
                            </span>
                            <i data-lucide="arrow-right" class="size-4"></i>
                        </a>
                        <a href="{{ route('profile.edit') }}"
                           class="{{ $activeProfile ? 'bg-gradient-to-r from-pink-500/15 to-violet-500/5 text-white border-l-2 border-pink-400 rounded-r-2xl rounded-l-md' : 'text-slate-400 hover:bg-white/5 hover:text-white border-l-2 border-transparent rounded-2xl' }} flex items-center gap-3 px-3 py-2.5 text-sm font-medium transition">
                            <i data-lucide="user" class="size-4"></i> Perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="mt-1 flex w-full items-center gap-3 rounded-2xl border-l-2 border-transparent px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:border-red-400 hover:bg-red-500/10 hover:text-red-300">
                                <i data-lucide="log-out" class="size-4"></i> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Tarjeta de racha --}}
                <div class="relative overflow-hidden rounded-2xl border border-pink-500/20 bg-gradient-to-br from-cyan-500/10 via-[#111528] to-pink-500/10 p-4">
                    <div aria-hidden="true" class="pointer-events-none absolute -right-6 -top-6 size-24 rounded-full bg-pink-500/20 blur-2xl"></div>
                    <div class="relative">
                        <p class="font-mono text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-300">Tu racha actual</p>
                        <div class="mt-2 flex items-end justify-between">
                            <div>
                                <p class="font-mono text-4xl font-bold text-white">{{ $racha }}</p>
                                <p class="text-[11px] text-slate-400">días seguidos</p>
                            </div>
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-orange-400 shadow-lg shadow-pink-500/40">
                                <i data-lucide="flame" class="size-6 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </aside>

        {{-- ===== CONTENIDO ===== --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-white/5 bg-[#070812]/75 px-4 backdrop-blur-xl sm:px-8">
                <div class="flex items-center gap-3">
                    <button type="button" id="btn-menu" class="rounded-xl p-2 text-slate-400 transition hover:bg-white/5 hover:text-white lg:hidden" aria-label="Abrir menú">
                        <i data-lucide="menu" class="size-5"></i>
                    </button>
                    <form action="{{ route('catalog.index') }}" method="GET" class="hidden sm:block">
                        <label class="relative block">
                            <span class="sr-only">Buscar anime</span>
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500"><i data-lucide="search" class="size-4"></i></span>
                            <input type="search" name="q" id="global-search" value="{{ request('q') }}" placeholder="Buscar anime..." autocomplete="off"
                                   class="h-10 w-64 rounded-full border border-white/10 bg-[#111827]/70 pl-10 pr-4 text-sm text-slate-200 outline-none transition placeholder:text-slate-500 focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
                            <div id="suggest-box" class="absolute left-0 top-12 z-50 hidden w-72 overflow-hidden rounded-2xl border border-white/10 bg-[#0b1120]/95 shadow-2xl shadow-cyan-500/10 backdrop-blur-xl"></div>
                        </label>
                    </form>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="btn-antispoiler" title="Modo anti-spoiler" aria-label="Modo anti-spoiler" class="rounded-xl p-2 text-lg transition hover:bg-white/5">🙈</button>
                    <a href="{{ route('dashboard') }}" class="relative rounded-xl p-2 text-slate-400 transition hover:bg-white/5 hover:text-white" aria-label="Notificaciones">
                        <i data-lucide="bell" class="size-5"></i>
                        <span class="absolute right-1.5 top-1.5 size-2 rounded-full bg-pink-400 ring-2 ring-[#070812]"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-full bg-gradient-to-br from-cyan-300 to-violet-400 font-mono text-xs font-bold text-[#070812]">{{ $iniciales }}</div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
                            <p class="font-mono text-[11px] text-slate-500">Otaku nivel {{ $totalLista }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden px-4 py-6 sm:px-8 sm:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- 📱 BOTTOM NAV MÓVIL (solo < lg) --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-white/5 bg-[#0b1120]/95 px-2 py-1.5 backdrop-blur-xl lg:hidden"
         style="padding-bottom: calc(.375rem + env(safe-area-inset-bottom));">
        @php
            $bottomNav = [
                ['route' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => 'Inicio'],
                ['route' => 'catalog.*', 'icon' => 'library', 'label' => 'Catálogo'],
                ['route' => 'news.*', 'icon' => 'newspaper', 'label' => 'Noticias'],
                ['route' => 'mylist.*', 'icon' => 'list-video', 'label' => 'Mi lista'],
                ['route' => 'achievements.*', 'icon' => 'trophy', 'label' => 'Logros'],
            ];
        @endphp
        @foreach($bottomNav as $item)
            @php
                $active = request()->routeIs($item['route']);
                $href = str_contains($item['route'], '*') ? route(str_replace('.*', '.index', $item['route'])) : route($item['route']);
            @endphp
            <a href="{{ $href }}"
               class="flex min-h-[48px] min-w-[56px] flex-col items-center justify-center gap-1 rounded-xl px-2 py-1.5 transition {{ $active ? 'text-pink-400' : 'text-slate-500' }}">
                <div class="relative">
                    @if($active)
                        <span class="absolute inset-0 rounded-full bg-pink-400/20 blur-md"></span>
                    @endif
                    <i data-lucide="{{ $item['icon'] }}" class="relative size-5"></i>
                </div>
                <span class="font-mono text-[9px] font-bold tracking-wide {{ $active ? 'text-pink-300' : 'text-slate-500' }}">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- 🎙️ Voz global (reposicionado sobre bottom nav) --}}
    <div class="fixed left-4 z-50 bottom-20 lg:bottom-4">
        <button id="btn-voice-cmd" title='Di "Naruto ya lo vi"' aria-label="Comando de voz"
                class="flex size-14 items-center justify-center rounded-full text-white shadow-lg shadow-pink-500/30 transition hover:scale-105"
                style="background: linear-gradient(135deg, #ec4899, #8b5cf6)">
            <i data-lucide="mic" class="size-6"></i>
        </button>
    </div>
    <div id="voice-toast" class="fixed bottom-40 left-1/2 z-50 hidden -translate-x-1/2 rounded-xl px-4 py-3 text-sm text-white lg:bottom-24" style="background: #111827; border: 1px solid #22c55e"></div>

    {{-- 🤖 ANIBOT --}}
    <div class="fixed bottom-20 right-4 z-50 flex flex-col items-end gap-3 lg:bottom-4">
        <div id="anibot-panel" class="hidden w-80 overflow-hidden rounded-3xl border border-white/10 bg-[#0b1120]/95 shadow-2xl shadow-cyan-500/10 backdrop-blur-xl">
            <div class="flex items-center gap-3 border-b border-white/10 bg-gradient-to-r from-cyan-500/10 to-pink-500/10 px-4 py-3">
                <span class="flex size-9 items-center justify-center rounded-full bg-gradient-to-br from-cyan-400 to-violet-500 text-lg">🤖</span>
                <div>
                    <p class="text-sm font-bold text-white">Anibot</p>
                    <p class="font-mono text-[10px] text-emerald-400">● en línea · 100% offline</p>
                </div>
                <button type="button" id="anibot-close" class="ml-auto rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white">✕</button>
            </div>

            <div id="anibot-log" class="flex h-80 flex-col gap-3 overflow-y-auto p-4"></div>

            <div class="flex flex-wrap gap-1.5 px-4 pb-2">
                @foreach([
                    '🌙 ¿Qué veo esta noche?' => '¿Qué veo esta noche?',
                    '🎲 Sorpréndeme' => 'Sorpréndeme',
                    '💎 Joyas ocultas' => 'Joyas ocultas',
                    '📅 Plan del finde' => 'Plan del fin de semana',
                    '📊 Mi resumen' => 'Mi resumen',
                ] as $label => $msg)
                    <button type="button" data-msg="{{ $msg }}" class="anibot-chip rounded-full border border-white/10 bg-[#111827]/80 px-2.5 py-1 font-mono text-[10px] font-bold text-slate-400 transition hover:border-cyan-400/50 hover:text-cyan-300">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <form id="anibot-form" class="flex gap-2 border-t border-white/10 p-3">
                <input type="text" id="anibot-input" autocomplete="off" placeholder="Escribe tu pregunta..."
                       class="h-9 flex-1 rounded-xl border border-white/10 bg-[#111827]/80 px-3 text-xs text-slate-200 outline-none placeholder:text-slate-600 focus:border-cyan-400">
                <button type="submit" class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 text-sm font-bold text-white transition hover:scale-105">➤</button>
            </form>
        </div>

        <button type="button" id="anibot-bubble" title="Chatea con Anibot" aria-label="Abrir Anibot"
                class="flex size-14 items-center justify-center rounded-full text-2xl shadow-lg shadow-cyan-500/30 transition hover:scale-105"
                style="background: linear-gradient(135deg, #06b6d4, #8b5cf6)">
            🤖
        </button>
    </div>

    {{-- 🏆 POPUP DE LOGROS --}}
    @if(session('new_achievements'))
        <div id="achievement-popup" class="fixed top-20 right-6 z-[60] flex w-80 flex-col gap-3">
            @foreach(session('new_achievements') as $a)
                <div class="achievement-toast flex items-center gap-4 rounded-2xl border border-amber-400/40 bg-slate-900/95 p-4 shadow-2xl shadow-amber-500/30 backdrop-blur-xl">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl text-2xl {{ $a->tierColor() }}">
                        {{ $a->icon }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-amber-400">🏆 Logro desbloqueado</p>
                        <p class="truncate text-sm font-bold text-white">{{ $a->name }}</p>
                        <p class="truncate text-xs text-slate-400">+{{ $a->points }} pts · {{ $a->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <style>
            .achievement-toast { animation: toast-in .6s cubic-bezier(.21,1.02,.73,1) forwards; }
            @keyframes toast-in {
                from { transform: translateX(120%) scale(.8); opacity: 0; }
                to { transform: translateX(0) scale(1); opacity: 1; }
            }
        </style>
        <script>
            setTimeout(() => document.getElementById('achievement-popup')?.remove(), 8000);
        </script>
    @endif

    <script>
        lucide.createIcons();

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        document.getElementById('btn-menu').onclick = () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        };
        document.getElementById('btn-close-sidebar').onclick = () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        };
        overlay.onclick = () => { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); };

        (function () {
            const b = document.getElementById('btn-antispoiler');
            const pintar = () => b.style.opacity = localStorage.getItem('antispoiler') === '1' ? '1' : '0.35';
            pintar();
            b.onclick = () => { localStorage.setItem('antispoiler', localStorage.getItem('antispoiler') === '1' ? '0' : '1'); pintar(); };
        })();

        (function () {
            const btn = document.getElementById('btn-voice-cmd');
            const toast = document.getElementById('voice-toast');
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR || !btn) return;
            const rec = new SR();
            rec.lang = 'es-ES';
            function showToast(msg, ok = true) {
                toast.textContent = msg;
                toast.style.borderColor = ok ? '#22c55e' : '#ef4444';
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 4000);
            }
            rec.onstart = () => showToast('🎙️ Te escucho... di "Naruto ya lo vi"');
            rec.onresult = async (e) => {
                const texto = e.results[0][0].transcript.trim();
                let m = texto.match(/^(.+?)\s+ya\s+(?:lo\s+|la\s+)?(?:vi|ví|he\s+visto)/i)
                     || texto.match(/^ya\s+(?:vi|ví)\s+(.+)$/i)
                     || texto.match(/^marcar\s+(.+?)\s+como\s+(?:visto|completado)/i);
                if (!m) { showToast('No entendí. Prueba: "Naruto ya lo vi"', false); return; }
                const titulo = m[1].trim();
                showToast('🔎 Buscando "' + titulo + '"...');
                try {
                    const res = await fetch("{{ route('mylist.voice') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ title: titulo })
                    });
                    const data = await res.json();
                    showToast(data.message, data.ok);
                } catch (err) { showToast('Error de conexión', false); }
            };
            btn.onclick = () => rec.start();
        })();

        // Autocompletado del buscador
        (function () {
            const input = document.getElementById('global-search');
            const box = document.getElementById('suggest-box');
            if (!input || !box) return;

            let timer = null;

            input.addEventListener('input', () => {
                clearTimeout(timer);
                const q = input.value.trim();
                if (q.length < 2) { box.classList.add('hidden'); box.innerHTML = ''; return; }

                timer = setTimeout(async () => {
                    try {
                        const res = await fetch("{{ route('catalog.suggest') }}?q=" + encodeURIComponent(q), {
                            headers: { 'Accept': 'application/json' }
                        });
                        const items = await res.json();
                        if (!items.length) { box.classList.add('hidden'); box.innerHTML = ''; return; }

                        box.innerHTML = items.map(a => `
                            <a href="/anime/${a.mal_id}" class="flex items-center gap-3 px-3 py-2 transition hover:bg-slate-800/70">
                                <img src="${a.image ?? ''}" alt="" class="h-10 w-7 shrink-0 rounded object-cover">
                                <span class="min-w-0 flex-1 truncate text-sm text-slate-200">${a.title}</span>
                                <span class="font-mono text-[10px] text-amber-400">★ ${a.score ?? '—'}</span>
                            </a>
                        `).join('');
                        box.classList.remove('hidden');
                    } catch (e) {
                        box.classList.add('hidden');
                    }
                }, 250);
            });

            document.addEventListener('click', (e) => {
                if (!box.contains(e.target) && e.target !== input) {
                    box.classList.add('hidden');
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') box.classList.add('hidden');
            });
        })();

        // ANIBOT
        (function () {
            const bubble = document.getElementById('anibot-bubble');
            const panel = document.getElementById('anibot-panel');
            const log = document.getElementById('anibot-log');
            const form = document.getElementById('anibot-form');
            const input = document.getElementById('anibot-input');
            if (!bubble || !panel) return;

            function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

            function addBot(text, cards = []) {
                const wrap = document.createElement('div');
                wrap.className = 'max-w-[85%] self-start whitespace-pre-line rounded-2xl rounded-bl-sm border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs leading-relaxed text-slate-200';
                wrap.textContent = text;
                log.appendChild(wrap);

                if (cards.length) {
                    const row = document.createElement('div');
                    row.className = 'flex gap-2 self-start overflow-x-auto pb-1';
                    cards.forEach(c => {
                        const a = document.createElement('a');
                        a.href = '/anime/' + c.mal_id;
                        a.className = 'w-24 shrink-0 rounded-xl border border-white/10 bg-[#111827] p-1.5 transition hover:border-cyan-400/40';
                        a.innerHTML = '<img src="' + esc(c.image) + '" class="h-28 w-full rounded-lg object-cover" alt="">' +
                                      '<p class="mt-1 truncate text-[10px] font-semibold text-slate-200">' + esc(c.title) + '</p>' +
                                      '<p class="font-mono text-[9px] text-amber-400">★ ' + (c.score ?? '—') + '</p>';
                        row.appendChild(a);
                    });
                    log.appendChild(row);
                }
                log.scrollTop = log.scrollHeight;
            }

            function addUser(text) {
                const wrap = document.createElement('div');
                wrap.className = 'max-w-[85%] self-end rounded-2xl rounded-br-sm bg-gradient-to-r from-pink-500/20 to-violet-500/20 px-3 py-2 text-xs text-slate-100';
                wrap.textContent = text;
                log.appendChild(wrap);
                log.scrollTop = log.scrollHeight;
            }

            async function send(msg) {
                msg = (msg || '').trim();
                if (!msg) return;
                addUser(msg);

                const thinking = document.createElement('div');
                thinking.className = 'self-start font-mono text-[10px] text-slate-500';
                thinking.textContent = '🤖 Anibot está pensando...';
                log.appendChild(thinking);
                log.scrollTop = log.scrollHeight;

                try {
                    const res = await fetch("{{ route('anibot.chat') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ message: msg })
                    });
                    const data = await res.json();
                    thinking.remove();
                    addBot(data.text || '...', data.cards || []);
                } catch (e) {
                    thinking.remove();
                    addBot('⚠️ No pude responderte ahora mismo. Intenta de nuevo.');
                }
            }

            bubble.onclick = () => {
                panel.classList.toggle('hidden');
                if (!panel.classList.contains('hidden') && !log.children.length) {
                    addBot('¡Hola! Soy Anibot 🤖 tu asistente anime 100% offline. Toca los botones de abajo o escríbeme lo que quieras.');
                }
                input?.focus();
            };
            document.getElementById('anibot-close').onclick = () => panel.classList.add('hidden');
            form.onsubmit = (e) => { e.preventDefault(); send(input.value); input.value = ''; };
            document.querySelectorAll('.anibot-chip').forEach(ch => ch.onclick = () => send(ch.dataset.msg));
        })();
    </script>
</body>
</html>