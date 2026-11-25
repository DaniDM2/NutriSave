<div class="mx-auto max-w-5xl px-6 py-16">
    <h1 class="text-3xl font-semibold tracking-tight">Peso</h1>
    <p class="mt-2 text-sm text-stone-600">
        Registro de peso y composición. El IMC se calcula con la altura de tu perfil.
    </p>

    @if ($status)
        <p class="mt-4 text-sm text-emerald-800">{{ $status }}</p>
    @endif

    @if ($isStale)
        <p class="mt-4 text-sm text-stone-600">
            Tu último peso es de hace {{ $daysSinceRecorded }} días (cadencia cada {{ $cadenceDays }}).
        </p>
    @endif

    @if (! $hasProfile)
        <p class="mt-6 text-sm text-stone-600">
            Completa tu perfil para registrar el peso.
        </p>
        <p class="mt-4">
            <a href="{{ route('profile') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
                Ir al perfil
            </a>
        </p>
    @else
        <form wire:submit="save" class="mt-8 max-w-md space-y-4">
            <div>
                <label for="weightKg" class="block text-sm font-medium text-stone-700">Peso (kg)</label>
                <input
                    id="weightKg"
                    type="number"
                    step="0.1"
                    min="20"
                    max="400"
                    wire:model="weightKg"
                    required
                    class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                >
                @error('weightKg')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="bodyFat" class="block text-sm font-medium text-stone-700">Grasa corporal (%)</label>
                <input
                    id="bodyFat"
                    type="number"
                    step="0.1"
                    min="0"
                    max="100"
                    wire:model="bodyFat"
                    class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                >
                @error('bodyFat')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="muscle" class="block text-sm font-medium text-stone-700">Músculo (%)</label>
                <input
                    id="muscle"
                    type="number"
                    step="0.1"
                    min="0"
                    max="100"
                    wire:model="muscle"
                    class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                >
                @error('muscle')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="water" class="block text-sm font-medium text-stone-700">Agua (%)</label>
                <input
                    id="water"
                    type="number"
                    step="0.1"
                    min="0"
                    max="100"
                    wire:model="water"
                    class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                >
                @error('water')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
            >
                Registrar peso
            </button>
        </form>

        <section class="mt-10">
            <h2 class="text-sm font-semibold tracking-wide text-stone-500 uppercase">Historial</h2>

            @if (count($snapshots) === 0)
                <p class="mt-3 text-sm text-stone-600">Aún no hay registros.</p>
            @else
                <ul class="mt-3 divide-y divide-stone-200 rounded-2xl border border-stone-200 bg-white">
                    @foreach ($snapshots as $snapshot)
                        <li wire:key="snapshot-{{ $snapshot['id'] }}" class="px-4 py-3">
                            <p class="text-sm font-medium text-stone-900">
                                {{ $snapshot['weightKg'] }} kg · IMC {{ $snapshot['bmi'] }}
                            </p>
                            <p class="text-sm text-stone-600">{{ $snapshot['recordedAt'] }}</p>
                            @if ($snapshot['bodyFat'] !== null || $snapshot['muscle'] !== null || $snapshot['water'] !== null)
                                <p class="mt-1 text-sm text-stone-500">
                                    @if ($snapshot['bodyFat'] !== null)
                                        Grasa {{ $snapshot['bodyFat'] }}%
                                    @endif
                                    @if ($snapshot['muscle'] !== null)
                                        · Músculo {{ $snapshot['muscle'] }}%
                                    @endif
                                    @if ($snapshot['water'] !== null)
                                        · Agua {{ $snapshot['water'] }}%
                                    @endif
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif
</div>
