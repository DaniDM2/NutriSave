<div class="mx-auto max-w-5xl px-6 py-16">
    <header class="mb-12">
        <p class="text-sm font-semibold tracking-wide text-emerald-700 uppercase">NutriSave</p>
        <h1 class="mt-2 text-4xl font-semibold tracking-tight">Stack inicial listo</h1>
        <p class="mt-3 max-w-2xl text-stone-600">
            Laravel 12 con arquitectura onion, Livewire 4, Alpine.js, Neuron AI, DOMPDF, Redis, Horizon y PostgreSQL.
        </p>
    </header>

    <section class="mb-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['Laravel 12', 'Framework y contenedor HTTP'],
            ['Onion', 'Domain → Application → Infrastructure'],
            ['Neuron AI', 'Agentes de IA en PHP'],
            ['DOMPDF', 'Generación de documentos PDF'],
            ['Redis + Horizon', 'Colas y procesamiento asíncrono'],
            ['PostgreSQL', 'Persistencia relacional determinista'],
            ['Livewire 4', 'UI reactiva en el servidor'],
            ['Alpine.js', 'Interactividad en el cliente (incluido en Livewire)'],
            ['Docker Compose', 'PHP-FPM, Nginx, Postgres, Redis, Horizon y Vite'],
        ] as [$title, $copy])
            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">{{ $title }}</h2>
                <p class="mt-1 text-sm text-stone-600">{{ $copy }}</p>
            </article>
        @endforeach
    </section>

    <section class="mb-10 grid gap-6 lg:grid-cols-2">
        <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Alpine.js</h2>
            <p class="mt-1 text-sm text-stone-600">Livewire ya incluye Alpine. No hace falta instalarlo por separado.</p>
            <div class="mt-4 flex items-center gap-3" x-data="{ open: false }">
                <button
                    type="button"
                    class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
                    @click="open = !open"
                >
                    Alternar mensaje
                </button>
                <p class="text-sm text-stone-700" x-show="open" x-cloak>Alpine está activo en esta página.</p>
            </div>
        </article>

        <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">PDF y colas</h2>
            <p class="mt-1 text-sm text-stone-600">Descarga síncrona con DOMPDF o encola el mismo trabajo en Horizon.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a
                    href="{{ route('pdf.sample') }}"
                    class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700"
                >
                    Descargar PDF
                </a>
                <button
                    type="button"
                    wire:click="enqueuePdf"
                    class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium hover:bg-stone-50"
                >
                    Encolar PDF
                </button>
            </div>
            @if ($status)
                <p class="mt-4 text-sm text-emerald-800">{{ $status }}</p>
            @endif
        </article>
    </section>

    <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Neuron AI</h2>
        @if ($aiConfigured)
            <p class="mt-1 text-sm text-stone-600">El asistente de nutrición usa el proveedor configurado en <code>.env</code>.</p>
        @else
            <p class="mt-1 text-sm text-amber-800">
                Define <code>NEURON_AI_PROVIDER</code> y la API key correspondiente para activar el chat.
            </p>
        @endif

        <form wire:submit="askAssistant" class="mt-4 space-y-3">
            <textarea
                wire:model="prompt"
                rows="3"
                class="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                placeholder="Pregunta sobre nutrición o desperdicio de alimentos"
            ></textarea>
            <button
                type="submit"
                class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
            >
                Preguntar
            </button>
        </form>

        @if ($reply)
            <div class="mt-4 rounded-xl bg-stone-50 p-4 text-sm whitespace-pre-wrap text-stone-800">{{ $reply }}</div>
        @endif
    </section>
</div>
