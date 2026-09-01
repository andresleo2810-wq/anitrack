<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AniTrack</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $user = auth()->user();
    $partes = collect(explode(' ', $user->name))->filter()->take(2);
    $iniciales = strtoupper($partes->map(fn($p) => $p[0])->implode(''));
    $totalLista = \App\Models\UserAnime::where('user_id', $user->id)->count();

    // 🔥 Racha real: días seguidos con actividad
    $fechas = \App\Models\UserAnime::where('user_id', $user->id)
        ->pluck('updated_at')->map(fn($f) => $f->toDateString())->unique()->flip();
    $dia = now()->startOfDay();
    if (!$fechas->has($dia->toDateString())) $dia->subDay();
    $racha = 0;
    while ($fechas->has($dia->toDateString())) { $racha++; $dia->subDay(); }
@endphp

<body class="min-h-screen bg-slate-950 text-slate-200 antialiased">
    {{-- Fondo manga (pon anitrack-manga-bg.png en /public cuando tengas la imagen) --}}
    <div class="fixed inset-0 -z-10 bg-[url('/anitrack-manga-bg.png')] bg-cover bg-center opacity-30"></div>
    <div class="fixed inset-0 -z-10 bg-gradient-to-b from-slate-950/40 via-slate-950/80 to-slate-950"></div>

    {{-- Overlay móvil --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/60 hidden lg:hidden"></div>

    <div class="flex min-h-screen">
        {{-- ===== SIDEBAR ===== --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-60 shrink-0 -translate-x-full overflow-y-auto border-r border-slate-800 bg-slate-950/95 backdrop-blur-xl transition-transform lg:static lg:translate-x-0 lg:bg-slate-950/80">
            <div class="flex h-16 items-center border-b border-slate-800 px-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-gradient-to-br from-pink-500 to-violet-500 text-lg font-bold text-white shadow-lg shadow-pink-500/20">✦</span>
                    <span class="text-xl font-bold tracking-tight text-white">Ani<span class="text-pink-400">Track</span></span>
                </a>
            </div>

            <nav class="flex flex-col gap-8 px-4 py-8">
                <div>
                    <p class="mb-4 px-3 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500">Menú principal</p>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('dashboard') }}"
                           class="{{ request()->routeIs('dashboard') ? 'bg-pink-500/20 text-pink-300' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }} flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition">
                            <span class="text-lg">⌂</span> Dashboard
                        </a>
                        <a href="{{ route('catalog.index') }}"
                           class="{{ request()->routeIs('catalog.*') ? 'bg-pink-500/20 text-pink-300' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }} flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition">
                            <span class="text-lg">▦</span> Catálogo
                        </a>
                        <a href="{{ route('mylist.index') }}"
                           class="{{ request()->routeIs('mylist.*') ? 'bg-pink-500/20 text-pink-300' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }} flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-medium transition">
                            <span class="flex items-center gap-3"><span class="text-lg">☷</span> Mi lista</span>
                            <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[10px] text-slate-400">{{ $totalLista }}</span>
                        </a>
                        <a href="{{ route('profile.edit') }}"
                           class="{{ request()->routeIs('profile.*') ? 'bg-pink-500/20 text-pink-300' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }} flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition">
                            <span class="text-lg">♙</span> Perfil
                        </a>
                    </div>
                </div>

                <div>
                    <p class="mb-4 px-3 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500">Tu espacio</p>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('recap') }}"
                           class="{{ request()->routeIs('recap') ? 'bg-violet-500/20 text-violet-300' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }} flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-medium transition">
                            <span class="flex items-center gap-3"><span class="text-lg">🏆</span> Recap {{ now()->year }}</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-700 bg-gradient-to-br from-cyan-400/10 to-pink-500/10 p-4">
                    <p class="text-xs font-semibold text-slate-300">Tu racha actual</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-3xl font-bold text-cyan-400">{{ $racha }}</span>
                        <span class="text-2xl text-pink-400">🔥</span>
                    </div>
                    <p class="mt-1 text-[11px] leading-4 text-slate-500">días seguidos registrando</p>
                </div>
            </nav>
        </aside>

        {{-- ===== CONTENIDO ===== --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-slate-800 bg-slate-950/70 px-5 backdrop-blur-xl sm:px-8">
                <div class="flex items-center gap-4">
                    <button type="button" id="btn-menu" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white lg:hidden" aria-label="Abrir menú">☰</button>

                    <form action="{{ route('catalog.index') }}" method="GET" class="hidden sm:block">
                        <label class="relative block">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500">⌕</span>
                            <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar anime..."
                                   class="h-10 w-64 rounded-full border border-slate-700 bg-slate-900/80 pl-10 pr-4 text-sm text-slate-200 outline-none transition placeholder:text-slate-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                        </label>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    {{-- 🙈 Anti-spoiler --}}
                    <button id="btn-antispoiler" title="Modo anti-spoiler" class="rounded-xl p-2 text-lg transition hover:bg-slate-800">🙈</button>

                    {{-- 🔔 Avisos --}}
                    <a href="{{ route('dashboard') }}" class="relative rounded-xl p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white" aria-label="Notificaciones">
                        🔔
                    </a>

                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-full bg-gradient-to-br from-cyan-300 to-violet-400 text-xs font-bold text-slate-950">{{ $iniciales }}</div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
                            <p class="text-[11px] text-slate-500">Otaku nivel {{ $totalLista }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-5 py-8 sm:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- 🎙️ Comando de voz global --}}
    <div class="fixed bottom-4 left-4 z-50">
        <button id="btn-voice-cmd" title='Di "Naruto ya lo vi"'
                class="w-14 h-14 rounded-full shadow-lg text-2xl text-white"
                style="background: linear-gradient(135deg, #ec4899, #8b5cf6)">🎙️</button>
    </div>
    <div id="voice-toast" class="hidden fixed bottom-24 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-xl shadow-lg text-white text-sm"
         style="background: #1a0b2e; border: 1px solid #22c55e"></div>

    <script>
        // Menú móvil
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        document.getElementById('btn-menu').onclick = () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        };
        overlay.onclick = () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        };

        // 🙈 Anti-spoiler
        (function () {
            const b = document.getElementById('btn-antispoiler');
            const pintar = () => b.style.opacity = localStorage.getItem('antispoiler') === '1' ? '1' : '0.35';
            pintar();
            b.onclick = () => {
                localStorage.setItem('antispoiler', localStorage.getItem('antispoiler') === '1' ? '0' : '1');
                pintar();
            };
        })();

        // 🎙️ Voz global
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

            rec.onstart = () => { btn.textContent = '🔴'; showToast('🎙️ Te escucho... di "Naruto ya lo vi"'); };
            rec.onend = () => { btn.textContent = '🎙️'; };
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
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ title: titulo })
                    });
                    const data = await res.json();
                    showToast(data.message, data.ok);
                } catch (err) { showToast('Error de conexión', false); }
            };
            btn.onclick = () => rec.start();
        })();
    </script>
</body>
</html>