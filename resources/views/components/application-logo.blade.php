<div x-data="{ theme: localStorage.getItem('anitrack-theme') || 'light' }"
     class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
    <button @click="localStorage.setItem('anitrack-theme','light'); location.reload()"
            title="Tema Claro"
            class="px-2 py-1 rounded text-sm transition"
            :class="theme === 'light' ? 'bg-yellow-200 shadow' : 'opacity-50 hover:opacity-100'">☀️</button>
    <button @click="localStorage.setItem('anitrack-theme','dark'); location.reload()"
            title="Tema Oscuro"
            class="px-2 py-1 rounded text-sm transition"
            :class="theme === 'dark' ? 'bg-gray-800 shadow' : 'opacity-50 hover:opacity-100'">🌙</button>
    <button @click="localStorage.setItem('anitrack-theme','otaku'); location.reload()"
            title="Tema Otaku"
            class="px-2 py-1 rounded text-sm transition"
            :class="theme === 'otaku' ? 'bg-pink-600 shadow' : 'opacity-50 hover:opacity-100'">🌸</button>
</div>