<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('logo-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#070812">
    <meta name="theme-color" content="#070812">
    <title>AniTrack · Inicia sesión</title>
    

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* 🔐 El autocompletado de Chrome no rompe el modo oscuro */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: #e2e8f0;
            -webkit-box-shadow: 0 0 0 1000px #0a0d1d inset;
            caret-color: #e2e8f0;
            transition: background-color 5000s ease-in-out 0s;
        }
        .auth-wrap { min-height: 100vh; min-height: 100svh; }
        /* Pantallas bajas / teclado abierto: compacta sin romper */
        @media (max-height: 720px) {
            .auth-wrap { padding-top: .75rem !important; padding-bottom: .75rem !important; }
            .auth-card { padding: 1.25rem !important; }
            .auth-hero { margin-top: 1rem !important; }
        }
    </style>
</head>
<body class="auth-wrap bg-[#070812] text-slate-200 antialiased">

    {{-- ✨ Halos de luz + cuadrícula sutil --}}
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-32 -top-32 size-[28rem] rounded-full bg-fuchsia-600/20 blur-[110px]"></div>
        <div class="absolute -right-40 top-1/3 size-[30rem] rounded-full bg-violet-600/15 blur-[120px]"></div>
        <div class="absolute -bottom-32 left-1/4 size-[26rem] rounded-full bg-cyan-500/10 blur-[110px]"></div>
        <div class="absolute inset-0 opacity-[0.045]"
             style="background-image: linear-gradient(rgba(255,255,255,.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.6) 1px, transparent 1px); background-size: 44px 44px;"></div>
    </div>

    <div class="relative mx-auto flex w-full max-w-6xl flex-col px-5 py-8 sm:px-8 lg:flex-row lg:items-center lg:gap-16 lg:px-12">

        {{-- 🎯 Branding (solo desktop) --}}
        <section class="hidden lg:flex lg:w-1/2 lg:flex-col lg:gap-8">
            <div class="flex items-center gap-3">
                                    <img src="{{ asset('logo-icon.png') }}" alt="AniTrack" class="size-10 rounded-2xl shadow-lg shadow-fuchsia-600/30">
                <span class="font-mono text-2xl font-bold tracking-tight text-white">Ani<span class="text-fuchsia-400">Track</span></span>
            </div>

            <div>
                <p class="font-mono text-[11px] font-bold uppercase tracking-[0.3em] text-fuchsia-400">Vuelve a tu historia</p>
                <h2 class="auth-hero mt-3 text-4xl font-extrabold leading-tight text-white xl:text-5xl">
                    Tu universo anime
                    <span class="bg-gradient-to-r from-fuchsia-400 via-pink-400 to-cyan-300 bg-clip-text text-transparent">te espera.</span>
                </h2>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-400">
                    Racha, logros, recomendaciones inteligentes y tu biblioteca local: todo sincronizado, todo tuyo, incluso sin conexión.
                </p>
            </div>

            <div class="max-w-md rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-white">Tu próxima aventura</p>
                    <span class="rounded-full bg-fuchsia-500/15 px-2.5 py-1 font-mono text-[10px] font-bold text-fuchsia-300">IA LOCAL</span>
                </div>
                <div class="mt-4 flex gap-3">
                    <div class="h-24 flex-1 rounded-xl bg-gradient-to-b from-fuchsia-500/50 to-violet-800/20"></div>
                    <div class="h-24 flex-1 rounded-xl bg-gradient-to-b from-violet-500/50 to-indigo-800/20"></div>
                    <div class="h-24 flex-1 rounded-xl bg-gradient-to-b from-cyan-400/40 to-blue-800/20"></div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Recomendaciones listas en cuanto entres.</p>
            </div>
        </section>

        {{-- 📋 Formulario --}}
        <section class="flex flex-1 justify-center lg:justify-end">
            <div class="auth-card w-full max-w-md rounded-3xl border border-white/10 bg-[#0d1022]/80 p-6 shadow-2xl shadow-fuchsia-900/20 backdrop-blur-xl sm:p-8">

                {{-- Logo móvil --}}
                <div class="mb-6 flex items-center gap-3 lg:hidden">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500 to-violet-600 shadow-lg shadow-fuchsia-600/30">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-4 text-white"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"/></svg>
                    </span>
                    <span class="font-mono text-xl font-bold text-white">Ani<span class="text-fuchsia-400">Track</span></span>
                </div>

                <p class="font-mono text-[11px] font-bold uppercase tracking-[0.28em] text-fuchsia-400">Bienvenido de vuelta</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Inicia sesión</h1>
                <p class="mt-1 text-sm text-slate-400">Continúa donde lo dejaste.</p>

                <form method="POST" action="{{ route('login') }}" class="mt-7 flex flex-col gap-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-300">Correo electrónico</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><path d="M4 6h16v12H4z" stroke-linejoin="round"/><path d="m4 7 8 6 8-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="tu@correo.com"
                                   class="h-[52px] w-full rounded-xl border border-white/10 bg-[#0a0d1d]/80 pl-11 pr-4 text-sm text-slate-100 outline-none transition placeholder:text-slate-600 focus:border-fuchsia-400/60 focus:ring-2 focus:ring-fuchsia-500/25">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label for="password" class="block text-xs font-semibold text-slate-300">Contraseña</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-medium text-fuchsia-400 underline-offset-4 transition hover:text-fuchsia-300 hover:underline">¿La olvidaste?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                            </span>
                            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••"
                                   class="h-[52px] w-full rounded-xl border border-white/10 bg-[#0a0d1d]/80 pl-11 pr-12 text-sm text-slate-100 outline-none transition placeholder:text-slate-600 focus:border-fuchsia-400/60 focus:ring-2 focus:ring-fuchsia-500/25">
                            <button type="button" id="toggle-pass" aria-label="Mostrar contraseña"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-500 transition hover:bg-white/5 hover:text-slate-300">
                                <svg id="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg id="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden size-5"><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><path d="M4 4l16 16" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember" class="flex items-center gap-2.5 text-sm text-slate-300">
                            <input id="remember" name="remember" type="checkbox" class="size-4 rounded border-white/20 bg-white/5 accent-fuchsia-500">
                            Recuérdame
                        </label>
                        <span class="flex items-center gap-1.5 text-[11px] text-slate-500">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-3.5"><path d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6z" stroke-linejoin="round"/></svg>
                            Acceso seguro
                        </span>
                    </div>

                    <button type="submit"
                            class="group flex h-[54px] w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-fuchsia-500 via-pink-500 to-violet-500 text-sm font-bold text-white shadow-lg shadow-fuchsia-600/30 transition hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fuchsia-400">
                        Entrar a AniTrack
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4 transition group-hover:translate-x-1"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </form>

                <div class="my-5 flex items-center gap-3">
                    <span class="h-px flex-1 bg-white/10"></span>
                    <span class="text-[11px] text-slate-500">o continúa con</span>
                    <span class="h-px flex-1 bg-white/10"></span>
                </div>

                <a href="{{ route('explore') }}"
                   class="group flex h-[52px] w-full items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/5 text-sm font-semibold text-slate-200 transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400">
                    Explorar sin cuenta
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4 transition group-hover:translate-x-1"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>

                <p class="mt-6 text-center text-sm text-slate-400">
                    ¿Aún no tienes cuenta?
                    <a href="{{ route('register') }}" class="ml-1 font-semibold text-fuchsia-400 underline-offset-4 transition hover:text-fuchsia-300 hover:underline">Regístrate gratis</a>
                </p>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('toggle-pass');
            const input = document.getElementById('password');
            const eye = document.getElementById('icon-eye');
            const eyeOff = document.getElementById('icon-eye-off');
            btn.addEventListener('click', () => {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                eye.classList.toggle('hidden', show);
                eyeOff.classList.toggle('hidden', !show);
                btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        })();
    </script>
</body>
</html>