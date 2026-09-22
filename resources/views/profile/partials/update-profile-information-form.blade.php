<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-300">Nombre</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   class="h-11 w-full rounded-xl border border-white/10 bg-[#0b0d1c] px-4 text-sm text-slate-200 outline-none transition focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
            <x-input-error class="mt-2" :messages="$errors->updateProfileInformation->get('name')" />
        </div>

        <div>
            <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-300">Correo electrónico</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   class="h-11 w-full rounded-xl border border-white/10 bg-[#0b0d1c] px-4 text-sm text-slate-200 outline-none transition focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
            <x-input-error class="mt-2" :messages="$errors->updateProfileInformation->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-amber-400/30 bg-amber-500/10 p-3 text-xs text-amber-300">
                    Tu correo no está verificado.
                    <button form="send-verification" class="font-bold underline underline-offset-4 transition hover:text-amber-200">
                        Reenviar enlace de verificación
                    </button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-emerald-400">Se envió un nuevo enlace a tu correo.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit"
                    class="rounded-2xl bg-gradient-to-r from-pink-500 via-fuchsia-500 to-violet-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-pink-500/30 transition hover:scale-[1.02] focus-visible:ring-2 focus-visible:ring-pink-400">
                Guardar cambios
            </button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="font-mono text-[11px] font-bold uppercase tracking-wider text-emerald-400">✓ Guardado</p>
            @endif
        </div>
    </form>
</section>