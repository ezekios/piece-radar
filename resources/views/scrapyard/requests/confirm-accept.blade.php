@php
    $part = $partHoldRequest->part;
    $vehicle = $part?->vehicle;
    $requestScrapyard = $vehicle?->scrapyard;
    $scrapyardName = $requestScrapyard?->name ?? $scrapyard?->name ?? 'Casse non renseignée';
    $scrapyardCity = $requestScrapyard?->city ?? $scrapyard?->city;
    $headerDescription = $scrapyardName . ($scrapyardCity ? ' · ' . $scrapyardCity : '');
    $displayTimezone = config('app.display_timezone', 'UTC');
    $createdAtDisplay = $partHoldRequest->created_at?->copy()->timezone($displayTimezone);

    $partStatusLabels = [
        'available' => 'Disponible',
        'preparing' => 'En préparation',
        'reserved' => 'Mise de côté',
        'sold' => 'Vendue',
        'unavailable' => 'Non disponible',
    ];

    $partStatusVariants = [
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

<x-layouts.scrapyard title="Confirmer l’acceptation - Pièce Radar" max-width="max-w-5xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Validation"
            title="Confirmer l’acceptation"
            :description="$headerDescription"
        >
            <x-slot:actions>
                <x-ui.badge variant="orange">Demande en attente</x-ui.badge>
            </x-slot:actions>
        </x-ui.page-header>

        <a href="{{ route('scrapyard.requests.show', $partHoldRequest) }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
            Retour vers la demande
        </a>
    </x-slot:header>

    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,0.58fr)_minmax(20rem,0.42fr)]">
        <div class="space-y-5">
            <x-ui.alert variant="warning" title="Validation requise">
                <p class="leading-6">
                    Vous êtes sur le point d’accepter cette demande. En confirmant, vous certifiez que la pièce est toujours disponible et peut être mise de côté pour le client. Après validation, les informations de contact nécessaires à la mise en relation pourront être débloquées.
                </p>
            </x-ui.alert>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Pièce demandée</h2>

                <div class="mt-4 flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                    <div class="min-w-0">
                        <p class="break-words text-2xl font-black leading-tight text-zinc-950">{{ $part?->name ?? 'Pièce non renseignée' }}</p>
                        <p class="mt-1 text-sm font-medium text-zinc-600">
                            {{ $conditionLabels[$part?->condition] ?? $part?->condition ?? 'État non précisé' }}
                        </p>
                    </div>

                    <div class="shrink-0 text-left min-[390px]:text-right">
                        <p class="text-3xl font-black text-[#FC8505]">
                            @if ($part?->price !== null)
                                {{ number_format((float) $part->price, 2, ',', ' ') }} €
                            @else
                                Prix sur demande
                            @endif
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2 min-[390px]:justify-end">
                            <x-ui.badge :variant="$partStatusVariants[$part?->status] ?? 'neutral'">
                                {{ $partStatusLabels[$part?->status] ?? $part?->status ?? 'Non renseigné' }}
                            </x-ui.badge>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Véhicule associé</h2>

                <dl class="mt-4 grid gap-3 text-sm min-[390px]:grid-cols-2">
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Marque</dt>
                        <dd class="mt-1 break-words font-black text-zinc-900">{{ $vehicle?->brand ?? 'Non renseignée' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Modèle</dt>
                        <dd class="mt-1 break-words font-black text-zinc-900">{{ $vehicle?->model ?? 'Non renseigné' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Année</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->year ?? 'Non renseignée' }}</dd>
                    </div>

                    @if ($vehicle?->engine)
                        <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                            <dt class="font-medium text-zinc-500">Motorisation</dt>
                            <dd class="mt-1 break-words font-black text-zinc-900">{{ $vehicle->engine }}</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Résumé de la demande</h2>

                <dl class="mt-4 grid gap-3 text-sm">
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Demande</dt>
                        <dd class="mt-1 font-black text-zinc-900">#{{ $partHoldRequest->id }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Statut</dt>
                        <dd class="mt-1 font-black text-zinc-900">En attente</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Reçue le</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $createdAtDisplay?->format('d/m/Y à H:i') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Action finale</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">
                    Seule cette validation passera réellement la demande en acceptée et déclenchera la réservation.
                </p>

                <div class="mt-4 grid gap-3">
                    <form method="POST" action="{{ route('scrapyard.requests.accept', $partHoldRequest) }}">
                        @csrf

                        <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full">
                            Confirmer l’acceptation
                        </x-ui.button>
                    </form>

                    <x-ui.button href="{{ route('scrapyard.requests.show', $partHoldRequest) }}" variant="secondary" size="lg" class="w-full">
                        Annuler
                    </x-ui.button>
                </div>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.scrapyard>
