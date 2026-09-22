<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('logo-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#070812">
        <title>{{ config('app.name', 'AniTrack') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased dark:text-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-none dark:bg-gradient-to-b dark:from-[#080d1a] dark:via-[#0b1120] dark:to-[#12182b]">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500 dark:text-pink-400" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg dark:bg-slate-900/80 dark:ring-1 dark:ring-slate-800 dark:shadow-pink-500/10 dark:backdrop-blur">
                {{ $slot }}
            </div>
        </div>

        <style>
            /* 🌸 Modo Sakura: responde si el switcher lo activa */
            html.theme-sakura body,
            html[data-theme="sakura"] body {
                background: linear-gradient(180deg, #3b1d34 0%, #1a1030 60%, #0b1120 100%) !important;
            }
            /* Inputs legibles en oscuro */
            html.dark input, html.dark select, html.dark textarea {
                background-color: #111827;
                border-color: #273244;
                color: #e2e8f0;
            }
            html.dark label, html.dark .text-gray-600 {
                color: #94a3b8;
            }
        </style>
            <script>
        (function () {
            const root = document.documentElement;
            const apply = (t) => {
                root.classList.toggle('dark', t !== 'light');
                root.classList.toggle('theme-sakura', t === 'sakura');
                localStorage.setItem('anitrack-theme', t);
            };

            // Captura clicks en los botones ☀️ 🌙  (dondequiera que estén inyectados)
            document.addEventListener('click', (e) => {
                const b = e.target.closest('button');
                if (!b) return;
                const t = (b.textContent || '').trim();
                if (t.includes('☀')) apply('light');
                else if (t.includes('🌙')) apply('dark');
                else if (t.includes('🌸')) apply('sakura');
            }, true);

            // Restaura tu preferencia al entrar
            const saved = localStorage.getItem('anitrack-theme');
            if (saved) apply(saved);
        })();
    </script>
    </body>
</html>