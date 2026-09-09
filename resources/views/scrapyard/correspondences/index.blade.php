@php
    $displayTimezone = config('app.display_timezone', 'UTC');
@endphp

<x-layouts.scrapyard title="Correspondances - Pièce Radar" max-width="max-w-6xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Espace casse"
            title="Correspondances"
            description="{{ $correspondences->count() }} opportunité{{ $correspondences->count() > 1 ? 's' : '' }} détectée{{ $correspondences->count() > 1 ? 's' : '' }} pour {{ $scrapyard->name }}"
        >
            <x-slot:actions>
                <x-ui.badge variant="success">
                    {{ $correspondences->count() }} correspondance{{ $correspondences->count() > 1 ? 's' : '' }}
                </x-ui.badge>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot:header>

    <x-ui.alert class="mt-5" variant="info">
        Un client recherche une pièce correspondant à votre stock. Aucune demande de réservation n’est créée automatiquement.
    </x-ui.alert>

    @if ($correspondences->isEmpty())
        <x-ui.empty-state
            class="mt-5"
            title="Aucune correspondance pour le moment."
            description="Les besoins clients compatibles avec vos pièces publiées apparaîtront ici."
        />
    @else
        <section class="mt-5 grid gap-4" aria-label="Correspondances détectées">
            @foreach ($correspondences as $correspondence)
                @php
                    $matchedPart = $correspondence->matchedPart;
                    $vehicle = $matchedPart?->vehicle;
                    $matchedAtDisplay = $correspondence->matched_at?->copy()->timezone($displayTimezone);
                    $partImage = $matchedPart?->images->first();
                @endphp

                <x-ui.card as="article" padding="p-4 sm:p-5">
                    <div class="grid gap-4 lg:grid-cols-[9rem_minmax(0,1fr)_minmax(14rem,0.36fr)] lg:items-start">
                        <div class="flex aspect-[4/3] w-full items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200 min-[390px]:max-w-56 lg:h-28 lg:w-36">
                            @if ($partImage)
                                <img src="{{ $partImage->url }}" alt="Photo {{ $matchedPart->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-12 w-16 rounded-lg border border-[#FC8505]/50 bg-white shadow-inner"></div>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between lg:block">
                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Besoin client correspondant</p>
                                    <h2 class="mt-2 break-words text-xl font-black leading-tight text-zinc-950">
                                        {{ $correspondence->part_name }}
                                    </h2>
                                    <p class="mt-1 break-words text-sm font-semibold text-zinc-700">
                                        {{ $correspondence->vehicle_brand }} {{ $correspondence->vehicle_model }}
                                        @if ($correspondence->vehicle_year)
                                            · {{ $correspondence->vehicle_year }}
                                        @endif
                                    </p>
                                </div>

                                <x-ui.badge class="shrink-0" variant="success">
                                    Correspondance trouvée
                                </x-ui.badge>
                            </div>

                            <dl class="mt-4 grid gap-3 text-sm min-[390px]:grid-cols-2">
                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="font-medium text-zinc-500">Pièce arrivée en stock</dt>
                                    <dd class="mt-1 break-words font-black text-zinc-900">{{ $matchedPart?->name ?? 'Pièce non renseignée' }}</dd>
                                </div>

                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="font-medium text-zinc-500">Véhicule donneur</dt>
                                    <dd class="mt-1 break-words font-black text-zinc-900">
                                        {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                        @if ($vehicle?->year)
                                            · {{ $vehicle->year }}
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex flex-col gap-3 rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200 lg:min-h-28 lg:justify-between">
                            <div>
                                <p class="text-xs font-bold text-zinc-500">Date de détection</p>
                                <p class="mt-1 text-sm font-black text-zinc-950">
                                    {{ $matchedAtDisplay?->format('d/m/Y à H:i') ?? 'Non renseignée' }}
                                </p>
                            </div>

                            @if ($matchedPart)
                                <x-ui.button href="{{ route('scrapyard.parts.show', $matchedPart) }}" variant="primary" size="md" class="w-full min-[390px]:w-auto lg:w-full">
                                    Voir la pièce
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </section>
    @endif
</x-layouts.scrapyard>
