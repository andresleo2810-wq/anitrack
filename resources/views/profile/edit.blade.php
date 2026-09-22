<x-app-layout>
    @php
        $user = auth()->user();
        $iniciales = strtoupper(collect(explode(' ', $user->name))->filter()->take(2)->map(fn($p) => $p[0])->implode(''));
    @endphp

    <div class="mx-auto flex max-w-4xl flex-col gap-8">

        {{-- HERO --}}
        <section class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="flex items-center gap-4">
                <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-300 to-violet-400 font-mono text-xl font-bold text-[#070812] shadow-lg shadow-cyan-500/30">
                    {{ $iniciales }}
                </div>
                <div class="min-w-0">
                    <p class="font-mono text-[11px] font-bold uppercase tracking-[0.3em] text-cyan-400">Tu cuenta</p>
                    <h1 class="mt-1 flex items-center gap-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        Perfil
                        <span class="inline-block size-2.5 rounded-full bg-gradient-to-br from-pink-400 to-fuchsia-500 shadow-lg shadow-pink-500/60" aria-hidden="true"></span>
                    </h1>
                    <p class="mt-1 truncate text-sm text-slate-400">{{ $user->email }}</p>
                </div>
            </div>
            <div class="rounded-2xl border border-white/5 bg-[#111528]/80 px-4 py-3 text-center backdrop-blur-xl">
                <p class="font-mono text-[10px] font-bold uppercase tracking-[0.22em] text-slate-500">Miembro desde</p>
                <p class="mt-1 font-mono text-sm font-bold text-white">{{ $user->created_at->format('M Y') }}</p>
            </div>
        </section>

        {{-- ℹ️ Información personal --}}
        <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl sm:p-8">
            <h2 class="flex items-center gap-2.5 text-lg font-bold text-white">
                <span class="h-5 w-1 rounded-full bg-gradient-to-b from-cyan-400 to-violet-500"></span>
                Información personal
            </h2>
            <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Nombre y correo de tu cuenta</p>
            <div class="mt-6 max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

        {{-- 🔑 Contraseña --}}
        <section class="rounded-2xl border border-white/5 bg-[#111528]/80 p-6 backdrop-blur-xl sm:p-8">
            <h2 class="flex items-center gap-2.5 text-lg font-bold text-white">
                <span class="h-5 w-1 rounded-full bg-gradient-to-b from-pink-400 to-fuchsia-500"></span>
                Cambiar contraseña
            </h2>
            <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Usa una contraseña larga y segura</p>
            <div class="mt-6 max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </section>

        {{-- ⚠️ Zona de peligro --}}
        <section class="rounded-2xl border border-red-400/20 bg-gradient-to-br from-red-500/5 via-[#111528] to-[#111528] p-6 backdrop-blur-xl sm:p-8">
            <h2 class="flex items-center gap-2.5 text-lg font-bold text-white">
                <span class="h-5 w-1 rounded-full bg-gradient-to-b from-red-400 to-pink-500"></span>
                Zona de peligro
            </h2>
            <p class="mt-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">Acciones irreversibles sobre tu cuenta</p>
            <div class="mt-6 max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </section>
    </div>
</x-app-layout>