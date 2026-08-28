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
                {{-- 🎙️ Comando de voz global --}}
        <div class="fixed bottom-4 left-4 z-50">
            <button id="btn-voice-cmd" title='Di "Naruto ya lo vi"'
                    class="w-14 h-14 rounded-full shadow-lg text-2xl text-white"
                    style="background: linear-gradient(135deg, #ec4899, #8b5cf6)">🎙️</button>
        </div>
        <div id="voice-toast"
             class="hidden fixed bottom-24 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-xl shadow-lg text-white text-sm"
             style="background: #1a0b2e; border: 1px solid #22c55e"></div>

        <script>
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

                if (!m) {
                    showToast('No entendí. Prueba: "Naruto ya lo vi"', false);
                    return;
                }

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
                } catch (err) {
                    showToast('Error de conexión', false);
                }
            };

            btn.onclick = () => rec.start();
        })();
        </script>
    </body>
</html>