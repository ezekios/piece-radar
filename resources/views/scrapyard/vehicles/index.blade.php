@php
    $displayTimezone = config('app.display_timezone', 'UTC');
@endphp

<x-layouts.scrapyard title="Véhicules - Pièce Radar">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Espace casse"
            title="Véhicules"
            description="{{ $scrapyard?->name ? 'Gérez les véhicules donneurs, leurs informations techniques et les pièces associées.' : 'La liste des véhicules sera disponible dès qu’une casse existera.' }}"
        >
            <x-slot:actions>
                <x-ui.badge variant="neutral">
                    {{ $vehicles->count() }} véhicule{{ $vehicles->count() > 1 ? 's' : '' }}
                </x-ui.badge>

                @if ($scrapyard)
                    <x-ui.button href="{{ route('scrapyard.vehicles.create') }}" variant="primary" size="md" class="w-full sm:w-auto">
                        Ajouter un véhicule
                    </x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        @if ($scrapyard)
            <p class="mt-2 text-sm font-semibold text-zinc-500">
                {{ $scrapyard->name }}@if ($scrapyard->city) · {{ $scrapyard->city }}@endif
            </p>
        @endif
    </x-slot:header>

    @if (! $scrapyard)
        <x-ui.empty-state
            class="mt-6"
            title="Aucune casse n’est disponible."
            description="La liste des véhicules s’affichera dès qu’une casse existera en base."
        />
    @else
        <x-ui.card class="mt-5" padding="p-4 sm:p-5">
            <form method="GET" action="{{ route('scrapyard.vehicles.index') }}">
                <x-ui.input
                    id="q"
                    name="q"
                    type="search"
                    label="Rechercher un véhicule"
                    value="{{ request('q') }}"
                    placeholder="Marque, modèle, année, immatriculation..."
                />

                <div class="mt-4 flex flex-col gap-2 min-[390px]:flex-row min-[390px]:items-center min-[390px]:justify-between">
                    <x-ui.button as="button" type="submit" variant="primary" size="md" class="w-full min-[390px]:w-auto">
                        Rechercher
                    </x-ui.button>

                    <x-ui.button href="{{ route('scrapyard.vehicles.index') }}" variant="ghost" size="md" class="w-full min-[390px]:w-auto">
                        Réinitialiser
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        @if ($vehicles->isEmpty())
            <x-ui.empty-state class="mt-5" title="Aucun véhicule trouvé pour le moment." />
        @else
            <section class="mt-5 grid gap-4 lg:grid-cols-2" aria-label="Véhicules de la casse">
                @foreach ($vehicles as $vehicle)
                    @php
                        $vehicleImage = $vehicle->images->first();
                        $createdAtDisplay = $vehicle->created_at?->copy()->timezone($displayTimezone);
                    @endphp

                    <x-ui.card as="article" padding="p-4 sm:p-5" class="flex h-full flex-col">
                        <div class="flex flex-col gap-4 min-[390px]:flex-row">
                            <div class="flex aspect-[4/3] w-full shrink-0 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200 min-[390px]:h-24 min-[390px]:w-28 sm:h-28 sm:w-32">
                                @if ($vehicleImage)
                                    <img src="{{ $vehicleImage->url }}" alt="Photo {{ $vehicle->brand }} {{ $vehicle->model }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-12 w-16 rounded-lg border border-[#FC8505]/50 bg-white shadow-inner"></div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                                    <div class="min-w-0">
                                        <h2 class="break-words text-lg font-black leading-tight text-zinc-950">
                                            {{ $vehicle->brand }} {{ $vehicle->model }}
                                        </h2>
                                        <p class="mt-1 text-sm font-semibold text-zinc-700">
                                            {{ $vehicle->year ?: 'Année non renseignée' }}
                                        </p>
                                        <p class="mt-1 break-words text-xs font-medium text-zinc-500">
                                            {{ $vehicle->license_plate ?: 'Immatriculation non renseignée' }}
                                        </p>
                                    </div>

                                    <div class="w-fit rounded-2xl bg-[#FC8505]/10 px-3 py-2 text-center">
                                        <p class="text-xl font-black text-[#FC8505]">{{ $vehicle->parts_count }}</p>
                                        <p class="text-[11px] font-black text-[#C96504]">Pièces</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <dl class="mt-4 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm min-[390px]:grid-cols-2 xl:grid-cols-3">
                            <div>
                                <dt class="text-xs font-bold text-zinc-500">Carburant</dt>
                                <dd class="mt-1 font-black text-zinc-950">{{ $vehicle->fuel ?: 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold text-zinc-500">Motorisation</dt>
                                <dd class="mt-1 font-black text-zinc-950">{{ $vehicle->engine ?: 'Non renseignée' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold text-zinc-500">Kilométrage</dt>
                                <dd class="mt-1 font-black text-zinc-950">
                                    @if ($vehicle->mileage)
                                        {{ number_format($vehicle->mileage, 0, ',', ' ') }} km
                                    @else
                                        Non renseigné
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-auto pt-4">
                            <p class="text-xs font-medium text-zinc-400">
                                Ajouté le {{ $createdAtDisplay?->format('d/m/Y à H:i') }}
                            </p>

                            <div class="mt-3 border-t border-zinc-100 pt-3">
                                <p class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Actions rapides</p>

                                <div class="mt-2 flex flex-col gap-2 min-[390px]:flex-row min-[390px]:flex-wrap">
                                    <x-ui.button href="{{ route('scrapyard.vehicles.show', $vehicle) }}" variant="secondary" size="md" class="w-full min-[390px]:w-auto">
                                        Voir le véhicule
                                    </x-ui.button>

                                    <x-ui.button href="{{ route('scrapyard.vehicles.parts.create', $vehicle) }}" variant="primary" size="md" class="w-full min-[390px]:w-auto">
                                        Ajouter une pièce
                                    </x-ui.button>

                                    @if ($vehicle->parts_count > 0)
                                        <x-ui.button href="{{ route('scrapyard.vehicles.show', $vehicle) }}" variant="ghost" size="md" class="w-full min-[390px]:w-auto">
                                            Voir les pièces associées
                                        </x-ui.button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </section>
        @endif
    @endif
</x-layouts.scrapyard>
