<div class="mx-auto flex min-h-screen max-w-md items-center px-6 py-16">
    <article class="w-full rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <p class="text-sm font-semibold tracking-wide text-emerald-700 uppercase">NutriSave</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">Invitación al hogar</h1>

        @if ($status)
            <p class="mt-4 text-sm {{ $found && ! $expired ? 'text-emerald-800' : 'text-red-700' }}">{{ $status }}</p>
        @endif

        @if ($found && ! $expired)
            <p class="mt-2 text-sm text-stone-600">
                Te han invitado a unirte a <span class="font-medium text-stone-900">{{ $householdName }}</span>
                con el correo <span class="font-medium text-stone-900">{{ $email }}</span>.
            </p>

            @guest
                <p class="mt-6 text-sm text-stone-600">Inicia sesión o crea una cuenta con ese correo para unirte.</p>
                <div class="mt-4 flex flex-col gap-3">
                    <a
                        href="{{ route('login', ['email' => $email]) }}"
                        class="rounded-lg bg-emerald-700 px-4 py-2 text-center text-sm font-medium text-white hover:bg-emerald-800"
                    >
                        Iniciar sesión
                    </a>
                    <a
                        href="{{ route('register', ['email' => $email]) }}"
                        class="rounded-lg border border-stone-300 px-4 py-2 text-center text-sm font-medium text-stone-700 hover:border-stone-400"
                    >
                        Crear cuenta
                    </a>
                </div>
            @else
                <form wire:submit="join" class="mt-6">
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
                    >
                        Unirme al hogar
                    </button>
                </form>
            @endguest
        @endif
    </article>
</div>
