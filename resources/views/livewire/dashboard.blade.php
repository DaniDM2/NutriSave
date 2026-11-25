<div class="mx-auto max-w-5xl px-6 py-16">
    <h1 class="text-3xl font-semibold tracking-tight">Inicio</h1>
    <p class="mt-4 flex gap-4">
        <a href="{{ route('household') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
            Ir al hogar
        </a>
        <a href="{{ route('profile') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
            Ir al perfil
        </a>
        <a href="{{ route('snapshots') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
            Ir al peso
        </a>
    </p>

    @if (! $hasProfile)
        <p class="mt-6 text-sm text-stone-600">
            Completa tu perfil para generar una dieta.
        </p>
    @elseif (! $hasSnapshot)
        <p class="mt-6 text-sm text-stone-600">
            Registra tu peso para generar una dieta.
        </p>
    @elseif ($isStale)
        <p class="mt-6 text-sm text-stone-600">
            Tu último peso es de hace {{ $daysSinceRecorded }} días (cadencia cada {{ $cadenceDays }}).
        </p>
    @else
        <p class="mt-6 text-sm text-stone-600">
            La generación de dieta llegará más adelante.
        </p>
    @endif

    <button
        type="button"
        disabled
        class="mt-4 cursor-not-allowed rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white opacity-50"
    >
        Generar dieta
    </button>
</div>
