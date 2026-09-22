<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="mb-1.5 block text-xs font-semibold text-slate-300">Contraseña actual</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="h-11 w-full rounded-xl border border-white/10 bg-[#0b0d1c] px-4 text-sm text-slate-200 outline-none transition focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
            <x-input-error class="mt-2" :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <label for="update_password_password" class="mb-1.5 block text-xs font-semibold text-slate-300">Nueva contraseña</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="h-11 w-full rounded-xl border border-white/10 bg-[#0b0d1c] px-4 text-sm text-slate-200 outline-none transition focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
            <x-input-error class="mt-2" :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="mb-1.5 block text-xs font-semibold text-slate-300">Confirmar contraseña</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="h-11 w-full rounded-xl border border-white/10 bg-[#0b0d1c] px-4 text-sm text-slate-200 outline-none transition focus:border-cyan-400/60 focus:ring-2 focus:ring-cyan-400/20">
            <x-input-error class="mt-2" :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit"
                    class="rounded-2xl bg-gradient-to-r from-pink-500 via-fuchsia-500 to-violet-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-pink-500/30 transition hover:scale-[1.02] focus-visible:ring-2 focus-visible:ring-pink-400">
                Actualizar contraseña
            </button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="font-mono text-[11px] font-bold uppercase tracking-wider text-emerald-400">✓ Guardado</p>
            @endif
        </div>
    </form>
</section>