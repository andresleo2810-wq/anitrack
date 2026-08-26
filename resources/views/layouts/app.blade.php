<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Tema inicial (evita parpadeo) -->
        <script>
            (function () {
                const theme = localStorage.getItem('anitrack-theme') || 'light';
                if (theme === 'dark' || theme === 'otaku') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.documentElement.setAttribute('data-theme', theme);
            })();
        </script>

        <style>
            /* ============ TEMA OTAKU (neón rosa-morado) ============ */
            [data-theme="otaku"] .min-h-screen {
                background: linear-gradient(160deg, #120425 0%, #1c0b3a 45%, #2b1055 100%);
            }
            [data-theme="otaku"] nav {
                background: rgba(20, 8, 40, .95) !important;
                border-color: rgba(217, 70, 239, .35) !important;
                backdrop-filter: blur(8px);
            }
            [data-theme="otaku"] .bg-white {
                background: #1a0b2e !important;
            }
            [data-theme="otaku"] .text-gray-900,
            [data-theme="otaku"] .text-gray-800 {
                color: #f5d0fe !important;
            }
            [data-theme="otaku"] .text-gray-600,
            [data-theme="otaku"] .text-gray-500 {
                color: #d8b4fe !important;
            }
            [data-theme="otaku"] .bg-indigo-600 {
                background: linear-gradient(90deg, #ec4899, #8b5cf6) !important;
            }
            [data-theme="otaku"] .bg-indigo-600:hover {
                filter: brightness(1.2);
            }
            [data-theme="otaku"] .shadow {
                box-shadow: 0 0 20px rgba(236, 72, 153, .25) !important;
            }
            [data-theme="otaku"] .text-indigo-600 {
                color: #f0abfc !important;
            }
            [data-theme="otaku"] header {
                background: rgba(20, 8, 40, .9) !important;
                border-color: rgba(217, 70, 239, .3) !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>