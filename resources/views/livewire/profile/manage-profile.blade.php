<div class="mx-auto max-w-5xl px-6 py-16">
    <h1 class="text-3xl font-semibold tracking-tight">Perfil</h1>
    <p class="mt-2 text-sm text-stone-600">
        Datos para calcular tus calorías. El peso se registra aparte.
    </p>

    @if ($status)
        <p class="mt-4 text-sm text-emerald-800">{{ $status }}</p>
    @endif

    <form wire:submit="save" class="mt-8 max-w-md space-y-4">
        <div>
            <label for="sex" class="block text-sm font-medium text-stone-700">Sexo</label>
            <select
                id="sex"
                wire:model="sex"
                required
                class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
            >
                <option value="">Selecciona</option>
                <option value="male">Hombre</option>
                <option value="female">Mujer</option>
            </select>
            @error('sex')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="birthDate" class="block text-sm font-medium text-stone-700">Fecha de nacimiento</label>
            <input
                id="birthDate"
                type="date"
                wire:model="birthDate"
                min="1900-01-01"
                required
                class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
            >
            @error('birthDate')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="heightCm" class="block text-sm font-medium text-stone-700">Altura (cm)</label>
            <input
                id="heightCm"
                type="number"
                wire:model="heightCm"
                min="50"
                max="250"
                required
                class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
            >
            @error('heightCm')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        @php
            $activityHints = [
                'sedentary' => ['Sedentario', 'Trabajo sentado, poco o nada de ejercicio.'],
                'light' => ['Ligero', 'Trabajo sentado y deporte 1–3 días por semana.'],
                'moderate' => ['Moderado', 'Trabajo sentado y deporte 4–5 días, o trabajo de pie.'],
                'active' => ['Activo', 'Trabajo físico o entrenamiento 6–7 días por semana.'],
                'very_active' => ['Muy activo', 'Trabajo físico y entrenamiento, o dos sesiones al día.'],
            ];
        @endphp
        <div class="group relative">
            <div class="flex items-center gap-1.5">
                <label for="activityLevel" class="block text-sm font-medium text-stone-700">Nivel de actividad</label>
                <button
                    type="button"
                    class="flex h-5 w-5 items-center justify-center rounded-full border border-stone-300 text-[11px] font-semibold text-stone-500 hover:border-stone-400 hover:text-stone-800 focus:border-emerald-700 focus:outline-none"
                    aria-label="Qué significa cada nivel de actividad"
                    aria-describedby="activity-level-tooltip"
                >
                    ?
                </button>
            </div>
            <span
                id="activity-level-tooltip"
                role="tooltip"
                class="pointer-events-none invisible absolute top-0 left-full z-20 ml-3 w-72 max-w-[calc(100vw-3rem)] rounded-xl border border-stone-200 bg-white p-3 text-left shadow-lg group-hover:visible group-focus-within:visible"
            >
                <ul class="space-y-2 text-xs text-stone-600">
                    @foreach ($activityHints as [$label, $hint])
                        <li>
                            <span class="font-medium text-stone-800">{{ $label }}.</span>
                            {{ $hint }}
                        </li>
                    @endforeach
                </ul>
            </span>
            <select
                id="activityLevel"
                wire:model.live="activityLevel"
                required
                class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
            >
                <option value="">Selecciona</option>
                @foreach ($activityHints as $value => [$label, $hint])
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @if (isset($activityHints[$activityLevel]))
                <p class="mt-1 text-sm text-stone-500">{{ $activityHints[$activityLevel][1] }}</p>
            @endif
            @error('activityLevel')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <p class="block text-sm font-medium text-stone-700">Deportes</p>
            @if (count($sports) > 0)
                <ul class="mt-2 divide-y divide-stone-200 rounded-2xl border border-stone-200 bg-white">
                    @foreach ($sports as $index => $sport)
                        <li wire:key="sport-{{ $index }}" class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-stone-900">{{ $sport }}</span>
                            <button
                                type="button"
                                wire:click="removeSport({{ $index }})"
                                class="text-sm font-medium text-stone-600 hover:text-stone-900"
                            >
                                Quitar
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
            @error('sports')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
            <div class="mt-2 flex gap-2">
                <input
                    id="sportDraft"
                    type="text"
                    wire:model="sportDraft"
                    maxlength="50"
                    placeholder="Añadir deporte"
                    class="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                >
                <button
                    type="button"
                    wire:click="addSport"
                    class="rounded-lg border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 hover:border-stone-400"
                >
                    Añadir
                </button>
            </div>
            @error('sportDraft')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="goal" class="block text-sm font-medium text-stone-700">Objetivo</label>
            <select
                id="goal"
                wire:model="goal"
                required
                class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
            >
                <option value="">Selecciona</option>
                <option value="lose">Perder peso</option>
                <option value="maintain">Mantener</option>
                <option value="gain">Ganar peso</option>
            </select>
            @error('goal')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="cadence" class="block text-sm font-medium text-stone-700">Cadencia de seguimiento</label>
            <select
                id="cadence"
                wire:model="cadence"
                required
                class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
            >
                <option value="">Selecciona</option>
                <option value="7">Cada 7 días</option>
                <option value="14">Cada 14 días</option>
                <option value="30">Cada 30 días</option>
            </select>
            @error('cadence')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
        >
            Guardar perfil
        </button>
    </form>
</div>
