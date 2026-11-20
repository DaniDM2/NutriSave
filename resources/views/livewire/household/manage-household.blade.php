<div class="mx-auto max-w-5xl px-6 py-16">
    <h1 class="text-3xl font-semibold tracking-tight">Hogar</h1>

    @if ($status)
        <p class="mt-4 text-sm text-emerald-800">{{ $status }}</p>
    @endif

    @if (! $hasHousehold)
        <p class="mt-2 text-sm text-stone-600">Crea un hogar para invitar a las personas con las que compartirás el plan.</p>

        <form wire:submit="create" class="mt-8 max-w-md space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-stone-700">Nombre del hogar</label>
                <input
                    id="name"
                    type="text"
                    wire:model="name"
                    maxlength="255"
                    required
                    class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
            >
                Crear hogar
            </button>
        </form>
    @else
        <p class="mt-2 text-lg text-stone-700">{{ $householdName }}</p>

        <section class="mt-8">
            <h2 class="text-sm font-semibold tracking-wide text-stone-500 uppercase">Miembros</h2>
            <ul class="mt-3 divide-y divide-stone-200 rounded-2xl border border-stone-200 bg-white">
                @foreach ($members as $member)
                    <li wire:key="member-{{ $member['email'] }}" class="flex items-center justify-between px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-stone-900">{{ $member['name'] }}</p>
                            <p class="text-sm text-stone-600">{{ $member['email'] }}</p>
                        </div>
                        <p class="text-sm text-stone-500">{{ $member['role'] === 'owner' ? 'Titular' : 'Miembro' }}</p>
                    </li>
                @endforeach
            </ul>
        </section>

        @if ($isOwner)
            <section class="mt-10">
                <h2 class="text-sm font-semibold tracking-wide text-stone-500 uppercase">Invitar</h2>
                <form wire:submit="invite" class="mt-3 max-w-md space-y-4">
                    <div>
                        <label for="inviteEmail" class="block text-sm font-medium text-stone-700">Correo</label>
                        <input
                            id="inviteEmail"
                            type="email"
                            wire:model="inviteEmail"
                            required
                            class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none"
                        >
                        @error('inviteEmail')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
                    >
                        Invitar
                    </button>
                </form>
            </section>

            @if (count($pendingEmails) > 0)
                <section class="mt-10">
                    <h2 class="text-sm font-semibold tracking-wide text-stone-500 uppercase">Invitaciones pendientes</h2>
                    <ul class="mt-3 divide-y divide-stone-200 rounded-2xl border border-stone-200 bg-white">
                        @foreach ($pendingEmails as $pendingEmail)
                            <li wire:key="pending-{{ $pendingEmail }}" class="px-4 py-3 text-sm text-stone-700">
                                {{ $pendingEmail }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endif
    @endif
</div>
