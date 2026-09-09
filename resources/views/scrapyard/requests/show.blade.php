@php
    $part = $partHoldRequest->part;
    $vehicle = $part?->vehicle;
    $requestScrapyard = $vehicle?->scrapyard;
    $client = $partHoldRequest->user;
    $canShowClientContact = in_array($partHoldRequest->status, ['accepted', 'completed'], true);
    $displayTimezone = config('app.display_timezone', 'UTC');
    $createdAtDisplay = $partHoldRequest->created_at?->copy()->timezone($displayTimezone);
    $reservedUntilDisplay = $partHoldRequest->reserved_until?->copy()->timezone($displayTimezone);
    $reservationRemaining = $partHoldRequest->reserved_until?->isFuture()
        ? now()
            ->diffAsCarbonInterval($partHoldRequest->reserved_until, true)
            ->cascade()
            ->locale(app()->getLocale())
            ->forHumans(['parts' => 2, 'join' => true])
        : null;

    $statusLabels = [
        'pending' => 'En attente',
        'accepted' => 'Acceptée',
        'refused' => 'Refusée',
        'cancelled' => 'Annulée',
        'completed' => 'Terminée',
        'expired' => 'Expirée',
    ];

    $statusVariants = [
        'pending' => 'orange',
        'accepted' => 'success',
        'refused' => 'danger',
        'cancelled' => 'neutral',
        'completed' => 'info',
        'expired' => 'orange',
    ];

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

<x-layouts.scrapyard title="Détail de la demande - Pièce Radar" max-width="max-w-6xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Demandes"
            title="Détail de la demande"
            description="Reçue le {{ $createdAtDisplay?->format('d/m/Y à H:i') }}"
        >
            <x-slot:actions>
                <x-ui.badge :variant="$statusVariants[$partHoldRequest->status] ?? 'neutral'">
                    {{ $statusLabels[$partHoldRequest->status] ?? $partHoldRequest->status }}
                </x-ui.badge>
            </x-slot:actions>
        </x-ui.page-header>

        <a href="{{ route('scrapyard.requests.index') }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
            Retour vers les demandes
        </a>
    </x-slot:header>

    <div class="mt-5 space-y-4">
        @if (session('success'))
            <x-ui.alert variant="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="error">
                {{ session('error') }}
            </x-ui.alert>
        @endif
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,0.64fr)_minmax(22rem,0.36fr)]">
        <div class="space-y-5">
            <x-ui.card as="section" padding="p-4 sm:p-5">
                <div class="flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Pièce demandée</p>
                        <h2 class="mt-2 break-words text-2xl font-black leading-tight text-zinc-950">
                            {{ $part?->name ?? 'Pièce non renseignée' }}
                        </h2>
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
                <h2 class="text-lg font-black text-zinc-950">Client</h2>

                @if ($canShowClientContact)
                    <dl class="mt-4 grid gap-3 text-sm min-[390px]:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                            <dt class="font-medium text-zinc-500">Nom</dt>
                            <dd class="mt-1 break-words font-black text-zinc-900">{{ $client?->name ?? 'Non renseigné' }}</dd>
                        </div>

                        <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                            <dt class="font-medium text-zinc-500">Téléphone</dt>
                            <dd class="mt-1 break-words font-black text-zinc-900">{{ $client?->phone ?? 'Non renseigné' }}</dd>
                        </div>

                        <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200 min-[390px]:col-span-2 lg:col-span-1">
                            <dt class="font-medium text-zinc-500">Email</dt>
                            <dd class="mt-1 break-words font-black text-zinc-900">{{ $client?->email ?? 'Non renseigné' }}</dd>
                        </div>
                    </dl>
                @else
                    <x-ui.alert class="mt-4" variant="neutral">
                        Les coordonnées du client seront disponibles après acceptation de la demande.
                    </x-ui.alert>
                @endif
            </x-ui.card>

            @if ($partHoldRequest->customer_message)
                <x-ui.card as="section" padding="p-4 sm:p-5">
                    <h2 class="text-lg font-black text-zinc-950">Message du client</h2>
                    <p class="mt-3 text-sm leading-6 text-zinc-700">{{ $partHoldRequest->customer_message }}</p>
                </x-ui.card>
            @endif

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

                    @if ($vehicle?->fuel)
                        <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                            <dt class="font-medium text-zinc-500">Carburant</dt>
                            <dd class="mt-1 break-words font-black text-zinc-900">{{ $vehicle->fuel }}</dd>
                        </div>
                    @endif

                    @if ($vehicle?->mileage)
                        <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                            <dt class="font-medium text-zinc-500">Kilométrage</dt>
                            <dd class="mt-1 font-black text-zinc-900">{{ number_format($vehicle->mileage, 0, ',', ' ') }} km</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            @if ($partHoldRequest->status === 'pending')
                <x-ui.card as="section" padding="p-4 sm:p-5">
                    <h2 class="text-lg font-black text-zinc-950">Traiter la demande</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">
                        L’acceptation passe par une étape de confirmation avant déblocage des coordonnées.
                    </p>

                    <div class="mt-4 grid gap-3">
                        <x-ui.button href="{{ route('scrapyard.requests.accept.confirm', $partHoldRequest) }}" variant="primary" size="lg" class="w-full">
                            Accepter la demande
                        </x-ui.button>

                        <form method="POST" action="{{ route('scrapyard.requests.refuse', $partHoldRequest) }}">
                            @csrf

                            <x-ui.button as="button" type="submit" variant="danger" size="lg" class="w-full">
                                Refuser la demande
                            </x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @endif

            @if ($partHoldRequest->status === 'accepted')
                <x-ui.card as="section" padding="p-4 sm:p-5">
                    <h2 class="text-lg font-black text-zinc-950">Réservation</h2>

                    @if ($partHoldRequest->reserved_until)
                        <p class="mt-3 text-sm font-black text-zinc-900">
                            Pièce réservée jusqu’au {{ $reservedUntilDisplay->format('d/m/Y à H:i') }}
                        </p>
                        <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">
                            La réservation est valable 48 heures après acceptation.
                            @if ($reservationRemaining)
                                Temps restant : {{ $reservationRemaining }}.
                            @else
                                La date limite est dépassée et sera traitée par l’expiration automatique.
                            @endif
                        </p>
                    @else
                        <p class="mt-3 text-sm font-medium leading-6 text-zinc-600">
                            La date limite de réservation n’est pas renseignée.
                        </p>
                    @endif
                </x-ui.card>

                <x-ui.card as="section" padding="p-4 sm:p-5">
                    <h2 class="text-lg font-black text-zinc-950">Gérer la mise de côté</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">
                        Terminez la demande si le client a récupéré la pièce, ou annulez la mise de côté si elle est abandonnée.
                    </p>

                    <div class="mt-4 grid gap-3">
                        <form
                            method="POST"
                            action="{{ route('scrapyard.requests.complete', $partHoldRequest) }}"
                            onsubmit="return confirm('Confirmer que la pièce a bien été récupérée par le client ? Cette action clôturera la demande.');"
                        >
                            @csrf

                            <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full">
                                Marquer comme terminée
                            </x-ui.button>
                        </form>

                        <form
                            method="POST"
                            action="{{ route('scrapyard.requests.cancel', $partHoldRequest) }}"
                            onsubmit="return confirm('Confirmer l’annulation de la mise de côté ? La pièce redeviendra disponible côté client.');"
                        >
                            @csrf

                            <x-ui.button as="button" type="submit" variant="danger" size="lg" class="w-full">
                                Annuler la mise de côté
                            </x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @endif

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Casse automobile</h2>

                <dl class="mt-4 grid gap-3 text-sm">
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Nom</dt>
                        <dd class="mt-1 break-words font-black text-zinc-900">{{ $requestScrapyard?->name ?? $scrapyard?->name ?? 'Non renseigné' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Ville</dt>
                        <dd class="mt-1 break-words font-black text-zinc-900">{{ $requestScrapyard?->city ?? $scrapyard?->city ?? 'Non renseignée' }}</dd>
                    </div>
                </dl>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.scrapyard>
