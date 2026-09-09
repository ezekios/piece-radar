@php
    $vehicle = $part->vehicle;
    $requestScrapyard = $vehicle?->scrapyard;
    $displayTimezone = config('app.display_timezone', 'UTC');
    $createdAtDisplay = $part->created_at?->copy()->timezone($displayTimezone);
    $updatedAtDisplay = $part->updated_at?->copy()->timezone($displayTimezone);

    $statusLabels = [
        'available' => 'Disponible',
        'reserved' => 'Mise de côté',
        'preparing' => 'En préparation',
        'sold' => 'Vendue',
        'unavailable' => 'Non disponible',
    ];

    $statusVariants = [
        'available' => 'success',
        'reserved' => 'orange',
        'preparing' => 'orange',
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

<x-layouts.scrapyard title="Détail de la pièce - Pièce Radar" max-width="max-w-6xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Pièces"
            title="{{ $part->name }}"
            description="{{ $requestScrapyard?->name ?? $scrapyard?->name ?? 'Casse non renseignée' }}{{ ($requestScrapyard?->city ?? $scrapyard?->city) ? ' · '.($requestScrapyard?->city ?? $scrapyard->city) : '' }}"
        >
            <x-slot:actions>
                <x-ui.badge :variant="$statusVariants[$part->status] ?? 'neutral'">
                    {{ $statusLabels[$part->status] ?? $part->status }}
                </x-ui.badge>
                <x-ui.badge :variant="$part->is_published ? 'success' : 'neutral'">
                    {{ $part->is_published ? 'Publiée' : 'Non publiée' }}
                </x-ui.badge>
            </x-slot:actions>
        </x-ui.page-header>

        <a href="{{ route('scrapyard.parts.index') }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
            Retour vers les pièces
        </a>
    </x-slot:header>

    @if (session('success'))
        <x-ui.alert class="mt-5" variant="success">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,0.62fr)_minmax(22rem,0.38fr)]">
        <div class="space-y-5">
            <x-ui.card as="section" padding="p-4 sm:p-5">
                <div class="flex flex-col gap-4 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Pièce</p>
                        <h2 class="mt-2 break-words text-2xl font-black leading-tight text-zinc-950">{{ $part->name }}</h2>
                        <p class="mt-2 text-sm font-medium text-zinc-600">
                            {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                        </p>
                    </div>

                    <div class="shrink-0 text-left min-[390px]:text-right">
                        <p class="text-3xl font-black text-[#FC8505]">
                            @if ($part->price !== null)
                                {{ number_format((float) $part->price, 2, ',', ' ') }} €
                            @else
                                Prix sur demande
                            @endif
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2 min-[390px]:justify-end">
                            <x-ui.badge :variant="$statusVariants[$part->status] ?? 'neutral'">
                                {{ $statusLabels[$part->status] ?? $part->status }}
                            </x-ui.badge>
                            <x-ui.badge :variant="$part->is_published ? 'success' : 'neutral'">
                                {{ $part->is_published ? 'Publiée' : 'Non publiée' }}
                            </x-ui.badge>
                        </div>
                    </div>
                </div>

                <dl class="mt-5 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm min-[390px]:grid-cols-2">
                    @if ($part->reference)
                        <div>
                            <dt class="font-medium text-zinc-500">Référence</dt>
                            <dd class="mt-1 break-words font-black text-zinc-900">{{ $part->reference }}</dd>
                        </div>
                    @endif

                    @if ($part->oem_reference)
                        <div>
                            <dt class="font-medium text-zinc-500">Référence OEM</dt>
                            <dd class="mt-1 break-words font-black text-zinc-900">{{ $part->oem_reference }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="font-medium text-zinc-500">Créée le</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $createdAtDisplay?->format('d/m/Y à H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-zinc-500">Mise à jour le</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $updatedAtDisplay?->format('d/m/Y à H:i') }}</dd>
                    </div>
                </dl>

                @if ($part->description)
                    <div class="mt-4 rounded-xl border border-zinc-100 bg-white p-3">
                        <p class="text-xs font-bold text-zinc-500">Description</p>
                        <p class="mt-1 text-sm leading-6 text-zinc-700">{{ $part->description }}</p>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-zinc-950">Photos de la pièce</h2>
                    <x-ui.badge variant="neutral">{{ $part->images->count() }}/5</x-ui.badge>
                </div>

                @if ($part->images->isEmpty())
                    <div class="mt-4 flex aspect-[4/3] w-full items-center justify-center rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                        <div class="h-16 w-24 rounded-lg border border-[#FC8505]/50 bg-white shadow-inner"></div>
                    </div>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-3 min-[430px]:grid-cols-3">
                        @foreach ($part->images as $image)
                            <img src="{{ $image->url }}" alt="Photo pièce {{ $loop->iteration }}" class="aspect-[4/3] w-full rounded-xl object-cover ring-1 ring-zinc-200">
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Véhicule donneur</h2>

                <dl class="mt-4 grid gap-3 text-sm min-[390px]:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Marque</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->brand ?? 'Non renseignée' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Modèle</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->model ?? 'Non renseigné' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Année</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->year ?? 'Non renseignée' }}</dd>
                    </div>

                    @if ($vehicle?->engine)
                        <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                            <dt class="font-medium text-zinc-500">Motorisation</dt>
                            <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->engine }}</dd>
                        </div>
                    @endif

                    @if ($vehicle?->fuel)
                        <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                            <dt class="font-medium text-zinc-500">Carburant</dt>
                            <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->fuel }}</dd>
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
            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Actions</h2>
                <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                    Préparez la fiche, gérez sa publication ou mettez à jour sa disponibilité.
                </p>

                <div class="mt-4 space-y-3">
                    <x-ui.button href="{{ route('scrapyard.parts.preparation.edit', $part) }}" variant="secondary" size="lg" class="w-full">
                        Préparer / vérifier la pièce
                    </x-ui.button>

                    @if (! $part->is_published || $part->status !== 'available')
                        <form method="POST" action="{{ route('scrapyard.parts.publish', $part) }}">
                            @csrf

                            <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full">
                                Publier la pièce
                            </x-ui.button>
                        </form>
                    @endif

                    @if ($part->is_published)
                        <form method="POST" action="{{ route('scrapyard.parts.unpublish', $part) }}">
                            @csrf

                            <x-ui.button as="button" type="submit" variant="secondary" size="lg" class="w-full">
                                Retirer de la publication
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Mise à jour du statut</h2>

                <form method="POST" action="{{ route('scrapyard.parts.updateStatus', $part) }}" class="mt-4 space-y-4">
                    @csrf

                    <x-ui.select
                        id="status"
                        name="status"
                        label="Nouveau statut"
                        value="{{ $part->status }}"
                        :options="$statusLabels"
                        required
                    />

                    <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full">
                        Mettre à jour le statut
                    </x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <h2 class="text-lg font-black text-zinc-950">Technique</h2>

                <dl class="mt-4 grid gap-3 text-sm min-[390px]:grid-cols-3 xl:grid-cols-1">
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">ID pièce</dt>
                        <dd class="mt-1 font-black text-zinc-900">#{{ $part->id }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">ID véhicule</dt>
                        <dd class="mt-1 font-black text-zinc-900">#{{ $part->vehicle_id }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Statut brut</dt>
                        <dd class="mt-1 break-words font-black text-zinc-900">({{ $part->status }})</dd>
                    </div>
                </dl>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.scrapyard>
