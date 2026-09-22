<section>
    <p class="text-sm leading-relaxed text-slate-400">
        Una vez eliminada tu cuenta, todos sus recursos y datos se borran permanentemente. Esta acción no se puede deshacer.
    </p>

    <button type="button" id="btn-open-delete"
            class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-red-400/30 bg-red-500/10 px-5 py-2.5 text-sm font-bold text-red-400 transition hover:bg-red-500/20 focus-visible:ring-2 focus-visible:ring-red-400">
        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.14 5.32a1.875 1.875 0 00-1.8-1.32H7.66a1.875 1.875 0 00-1.8 1.32l-.82 3.99m13.5-3.99H5.25"/></svg>
        Eliminar cuenta
    </button>

    <dialog id="delete-dialog" class="w-full max-w-md rounded-2xl border border-red-400/30 bg-[#111528] p-6 text-slate-200 shadow-2xl shadow-red-500/20 backdrop-blur-xl">
        <h3 class="text-lg font-bold text-white">¿Eliminar tu cuenta?</h3>
        <p class="mt-2 text-sm text-slate-400">
            Esta acción es permanente. Confirma tu contraseña para continuar.
        </p>

        <form method="post" action="{{ route('profile.destroy') }}" class="mt-5 space-y-4">
            @csrf
            @method('delete')

            <div>
                <label for="delete_password" class="mb-1.5 block text-xs font-semibold text-slate-300">Contraseña</label>
                <input id="delete_password" name="password" type="password" placeholder="Tu contraseña"
                       class="h-11 w-full rounded-xl border border-white/10 bg-[#0b0d1c] px-4 text-sm text-slate-200 outline-none transition placeholder:text-slate-600 focus:border-red-400/60 focus:ring-2 focus:ring-red-400/20">
                <x-input-error class="mt-2" :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="flex justify-end gap-3 pt-1">
                <button type="button" id="btn-cancel-delete"
                        class="rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/10">
                    Cancelar
                </button>
                <button type="submit"
                        class="rounded-2xl bg-gradient-to-r from-red-500 to-pink-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-500/30 transition hover:scale-[1.02]">
                    Sí, eliminar
                </button>
            </div>
        </form>
    </dialog>

    <script>
        (function () {
            const dialog = document.getElementById('delete-dialog');
            document.getElementById('btn-open-delete').onclick = () => dialog.showModal();
            document.getElementById('btn-cancel-delete').onclick = () => dialog.close();
            dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });

            // Si hubo error de validación, reabre el diálogo con el mensaje
            @if($errors->userDeletion->isNotEmpty())
                dialog.showModal();
            @endif
        })();
    </script>
</section>