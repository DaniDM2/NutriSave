<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
        @auth
            <header class="border-b border-stone-200 bg-white">
                <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold tracking-wide text-emerald-700 uppercase">
                        NutriSave
                    </a>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-stone-700">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="text-sm font-medium text-stone-600 hover:text-stone-900"
                            >
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth
        {{ $slot }}
        @livewireScripts
    </body>
</html>
