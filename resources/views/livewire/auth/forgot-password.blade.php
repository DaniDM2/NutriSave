<div class="mx-auto flex min-h-screen max-w-md items-center px-6 py-16">
    <article class="w-full rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <p class="text-sm font-semibold tracking-wide text-emerald-700 uppercase">NutriSave</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">Restablecer contraseña</h1>
        <p class="mt-2 text-sm text-stone-600">Te enviaremos un enlace si el correo está registrado.</p>

        <form wire:submit="sendResetLink" class="mt-6 space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-stone-700">Correo</label>
                <input
                    id="email"
                    type="email"
                    wire:model="email"
                    autocomplete="username"
                    required
                    class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            @if ($status)
                <p class="text-sm text-emerald-800">{{ $status }}</p>
            @endif

            <button
                type="submit"
                class="w-full rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
            >
                Enviar enlace
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-600">
            <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:text-emerald-800">Volver a iniciar sesión</a>
        </p>
    </article>
</div>
