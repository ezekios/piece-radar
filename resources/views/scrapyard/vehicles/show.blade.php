@php
    $displayTimezone = config('app.display_timezone', 'UTC');
    $createdAtDisplay = $vehicle->created_at?->copy()->timezone($displayTimezone);
    $updatedAtDisplay = $vehicle->updated_at?->copy()->timezone($displayTimezone);

    $statusLabels = [
        'available' => 'Disponible',
        'preparing' => 'En préparation',
        'reserved' => 'Mise de côté',
        'sold' => 'Vendue',
        'unavailable' => 'Non disponible',
    ];

    $statusVariants = [
        'available' => 'success',
        'preparing' => 'orange',
        'reserved' => 'orange',
        'sold' => 'info',
        'unavailable' => 'neutral',
    ];

    $conditionLabels = [
        'unknown' => 'État non précisé',
        'used_good' => 'Occasion bon état',
        'used_average' => 'Occasion état moyen',
        'damaged' => 'Endommagée',
    ];
@endphp

<x-layouts.scrapyard title="Détail du véhicule - Pièce Radar">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Véhicules"
            title="{{ $vehicle->brand }} {{ $vehicle->model }}"
            description="{{ $scrapyard->name }}{{ $scrapyard->city ? ' · '.$scrapyard->city : '' }}"
        >
            <x-slot:actions>
                <x-ui.badge variant="neutral">
                    {{ $vehicle->parts->count() }} pièce{{ $vehicle->parts->count() > 1 ? 's' : '' }}
                </x-ui.badge>

                <x-ui.button href="{{ route('scrapyard.vehicles.edit', $vehicle) }}" variant="secondary" size="md" class="w-full sm:w-auto">
                    Modifier le véhicule
                </x-ui.button>

                <x-ui.button href="{{ route('scrapyard.vehicles.parts.create', $vehicle) }}" variant="primary" size="md" class="w-full sm:w-auto">
                    Ajouter une pièce
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <a href="{{ route('scrapyard.vehicles.index') }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
            Retour vers les véhicules
        </a>
    </x-slot:header>

    @if (session('success'))
        <x-ui.alert class="mt-5" variant="success">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
        <div class="space-y-5">
            <x-ui.card as="section" padding="p-4 sm:p-5">
                <div class="flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Véhicule donneur</p>
                        <h2 class="mt-2 break-words text-2xl font-black leading-tight text-zinc-950">
                            {{ $vehicle->brand }} {{ $vehicle->model }}
                        </h2>
                        <p class="mt-2 break-words text-sm font-medium text-zinc-600">
                            {{ $vehicle->license_plate ?: 'Immatriculation non renseignée' }}
                        </p>
                    </div>

                    @if ($vehicle->year)
                        <x-ui.badge variant="orange">{{ $vehicle->year }}</x-ui.badge>
                    @endif
                </div>

                <dl class="mt-5 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm min-[390px]:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <dt class="font-medium text-zinc-500">Marque</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->brand }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-zinc-500">Modèle</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->model }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-zinc-500">Année</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->year ?? 'Non renseignée' }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-zinc-500">Carburant</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->fuel ?: 'Non renseigné' }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-zinc-500">Motorisation</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->engine ?: 'Non renseignée' }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-zinc-500">Kilométrage</dt>
                        <dd class="mt-1 font-black text-zinc-900">
                            @if ($vehicle->mileage)
                                {{ number_format($vehicle->mileage, 0, ',', ' ') }} km
                            @else
                                Non renseigné
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-zinc-500">Date d’ajout</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $createdAtDisplay?->format('d/m/Y à H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-zinc-500">Mise à jour</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $updatedAtDisplay?->format('d/m/Y à H:i') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-zinc-950">Photos du véhicule</h2>
                    <x-ui.badge variant="neutral">{{ $vehicle->images->count() }}/5</x-ui.badge>
                </div>

                @if ($vehicle->images->isEmpty())
                    <div class="mt-4 flex aspect-[4/3] w-full items-center justify-center rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                        <div class="h-16 w-24 rounded-lg border border-[#FC8505]/50 bg-white shadow-inner"></div>
                    </div>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-3 min-[430px]:grid-cols-3">
                        @foreach ($vehicle->images as $image)
                            <img src="{{ $image->url }}" alt="Photo véhicule {{ $loop->iteration }}" class="aspect-[4/3] w-full rounded-xl object-cover ring-1 ring-zinc-200">
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>

        <section>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-xl font-black text-zinc-950">Pièces associées</h2>
                <x-ui.badge variant="neutral">{{ $vehicle->parts->count() }}</x-ui.badge>
            </div>

            @if ($vehicle->parts->isEmpty())
                <x-ui.empty-state title="Aucune pièce associée à ce véhicule pour le moment." />
            @else
                <div class="space-y-3">
                    @foreach ($vehicle->parts as $part)
                        @php
                            $status = $part->status;
                            $partImage = $part->images->first();
                        @endphp

                        <x-ui.card as="article" padding="p-4">
                            <div class="flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                                <div class="flex min-w-0 gap-3">
                                    <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                                        @if ($partImage)
                                            <img src="{{ $partImage->url }}" alt="Photo {{ $part->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="h-10 w-14 rounded-md border border-[#FC8505]/50 bg-white shadow-inner"></div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="break-words text-base font-black text-zinc-950">
                                            {{ $part->name }}
                                        </h3>
                                        <p class="mt-1 text-xs font-medium text-zinc-500">
                                            {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0 text-left min-[390px]:text-right">
                                    <p class="text-xl font-black text-[#FC8505]">
                                        @if ($part->price !== null)
                                            {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                        @else
                                            Prix sur demande
                                        @endif
                                    </p>
                                    <x-ui.badge class="mt-1" :variant="$statusVariants[$status] ?? 'neutral'">
                                        {{ $statusLabels[$status] ?? $status }}
                                    </x-ui.badge>
                                </div>
                            </div>

                            <dl class="mt-4 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm min-[390px]:grid-cols-3">
                                <div>
                                    <dt class="text-xs font-bold text-zinc-500">Publication</dt>
                                    <dd class="mt-1 font-black text-zinc-950">{{ $part->is_published ? 'Publiée' : 'Non publiée' }}</dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-bold text-zinc-500">Référence</dt>
                                    <dd class="mt-1 break-words font-black text-zinc-950">{{ $part->reference ?: 'Non renseignée' }}</dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-bold text-zinc-500">Référence OEM</dt>
                                    <dd class="mt-1 break-words font-black text-zinc-950">{{ $part->oem_reference ?: 'Non renseignée' }}</dd>
                                </div>
                            </dl>

                            <div class="mt-3 flex justify-end border-t border-zinc-100 pt-3">
                                <x-ui.button href="{{ route('scrapyard.parts.show', $part) }}" variant="secondary" size="sm" class="w-full min-[390px]:w-auto">
                                    Voir la pièce
                                </x-ui.button>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.scrapyard>
