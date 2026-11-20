<div class="mx-auto flex min-h-screen max-w-md items-center px-6 py-16">
    <article class="w-full rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <p class="text-sm font-semibold tracking-wide text-emerald-700 uppercase">NutriSave</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">Iniciar sesión</h1>
        <p class="mt-2 text-sm text-stone-600">Entra con tu correo y contraseña.</p>

        <form wire:submit="authenticate" class="mt-6 space-y-4">
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

            <div>
                <label for="password" class="block text-sm font-medium text-stone-700">Contraseña</label>
                <input
                    id="password"
                    type="password"
                    wire:model="password"
                    autocomplete="current-password"
                    required
                    class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-stone-700">
                <input type="checkbox" wire:model="remember" class="rounded border-stone-300 text-emerald-700">
                Recordarme
            </label>

            <button
                type="submit"
                class="w-full rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
            >
                Iniciar sesión
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-600">
            <a href="{{ route('password.request') }}" class="font-medium text-emerald-700 hover:text-emerald-800">¿Olvidaste tu contraseña?</a>
        </p>
        <p class="mt-2 text-center text-sm text-stone-600">
            ¿No tienes cuenta?
            <a href="{{ route('register', array_filter(['email' => $email])) }}" class="font-medium text-emerald-700 hover:text-emerald-800">Crear cuenta</a>
        </p>
    </article>
</div>
