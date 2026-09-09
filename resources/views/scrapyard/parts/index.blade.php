@php
    $statusLabels = [
        'available' => 'Disponible',
        'reserved' => 'Mise de côté',
        'sold' => 'Vendue',
        'unavailable' => 'Non disponible',
        'preparing' => 'En préparation',
    ];

    $statusVariants = [
        'available' => 'success',
        'reserved' => 'orange',
        'sold' => 'info',
        'unavailable' => 'neutral',
        'preparing' => 'orange',
    ];

    $conditionLabels = [
        'unknown' => 'État non précisé',
        'used_good' => 'Occasion bon état',
        'used_average' => 'Occasion état moyen',
        'damaged' => 'Endommagée',
    ];

    $displayTimezone = config('app.display_timezone', 'UTC');

    $publicationFilters = [
        ['label' => 'Toutes les pièces', 'value' => null],
        ['label' => 'Publiées', 'value' => 'published'],
        ['label' => 'Non publiées', 'value' => 'unpublished'],
    ];
@endphp

<x-layouts.scrapyard title="Pièces - Pièce Radar">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Espace casse"
            title="Pièces"
            description="{{ $scrapyard?->name ? 'Préparez, publiez et suivez les pièces issues de vos véhicules donneurs.' : 'La liste des pièces sera disponible dès qu’une casse existera.' }}"
        >
            <x-slot:actions>
                <x-ui.badge variant="neutral">
                    {{ $parts->count() }} pièce{{ $parts->count() > 1 ? 's' : '' }}
                </x-ui.badge>
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
            description="La liste des pièces s’affichera dès qu’une casse existera en base."
        />
    @else
        <section class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(18rem,0.35fr)]">
            <x-ui.card padding="p-4 sm:p-5">
                <form method="GET" action="{{ route('scrapyard.parts.index') }}">
                    <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_14rem]">
                        <x-ui.input
                            id="q"
                            name="q"
                            type="search"
                            label="Rechercher une pièce"
                            value="{{ request('q') }}"
                            placeholder="Nom, référence, marque, modèle..."
                        />

                        <x-ui.select
                            id="status"
                            name="status"
                            label="Statut"
                            value="{{ request('status') }}"
                            :options="$statusLabels"
                            placeholder="Tous les statuts"
                        />
                    </div>

                    @if ($activePublication)
                        <input type="hidden" name="publication" value="{{ $activePublication }}">
                    @endif

                    <div class="mt-4 flex flex-col gap-2 min-[390px]:flex-row min-[390px]:items-center min-[390px]:justify-between">
                        <x-ui.button as="button" type="submit" variant="primary" size="md" class="w-full min-[390px]:w-auto">
                            Rechercher
                        </x-ui.button>

                        <x-ui.button href="{{ route('scrapyard.parts.index') }}" variant="ghost" size="md" class="w-full min-[390px]:w-auto">
                            Réinitialiser
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card padding="p-4 sm:p-5">
                <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Publication</p>

                <div class="mt-3 flex flex-col gap-2 min-[390px]:flex-row min-[390px]:flex-wrap xl:flex-col">
                    @foreach ($publicationFilters as $filter)
                        @php
                            $isActive = $activePublication === $filter['value'];
                            $filterParameters = request()
                                ->only(['q', 'status'])
                                + ($filter['value'] ? ['publication' => $filter['value']] : []);
                        @endphp

                        <x-ui.button
                            href="{{ route('scrapyard.parts.index', $filterParameters) }}"
                            :variant="$isActive ? 'primary' : 'secondary'"
                            size="sm"
                            class="w-full min-[390px]:w-auto xl:w-full"
                        >
                            {{ $filter['label'] }}
                        </x-ui.button>
                    @endforeach
                </div>
            </x-ui.card>
        </section>

        @if ($parts->isEmpty())
            <x-ui.empty-state class="mt-5" title="Aucune pièce trouvée pour le moment." />
        @else
            <section class="mt-5 grid gap-4 xl:grid-cols-2" aria-label="Pièces de la casse">
                @foreach ($parts as $part)
                    @php
                        $vehicle = $part->vehicle;
                        $status = $part->status;
                        $partImage = $part->images->first();
                        $createdAtDisplay = $part->created_at?->copy()->timezone($displayTimezone);
                    @endphp

                    <x-ui.card as="article" padding="p-4 sm:p-5" class="flex h-full flex-col">
                        <div class="flex flex-col gap-4 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                            <div class="flex min-w-0 gap-3">
                                <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                                    @if ($partImage)
                                        <img src="{{ $partImage->url }}" alt="Photo {{ $part->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-10 w-14 rounded-md border border-[#FC8505]/50 bg-white shadow-inner"></div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <h2 class="break-words text-lg font-black leading-tight text-zinc-950">
                                        {{ $part->name }}
                                    </h2>
                                    <p class="mt-1 text-sm font-semibold text-zinc-700">
                                        {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                        @if ($vehicle?->year)
                                            · {{ $vehicle->year }}
                                        @endif
                                    </p>
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

                                <div class="mt-2 flex flex-wrap gap-2 min-[390px]:justify-end">
                                    <x-ui.badge :variant="$statusVariants[$status] ?? 'neutral'">
                                        {{ $statusLabels[$status] ?? $status }}
                                    </x-ui.badge>
                                    <x-ui.badge :variant="$part->is_published ? 'success' : 'neutral'">
                                        {{ $part->is_published ? 'Publiée' : 'Non publiée' }}
                                    </x-ui.badge>
                                </div>
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

                        <div class="mt-auto pt-4">
                            <p class="text-xs font-medium text-zinc-400">
                                Créée le {{ $createdAtDisplay?->format('d/m/Y à H:i') }}
                            </p>

                            <div class="mt-3 border-t border-zinc-100 pt-3">
                                <p class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Actions rapides</p>

                                <div class="mt-2 flex flex-col gap-2 min-[390px]:flex-row min-[390px]:flex-wrap">
                                    <x-ui.button href="{{ route('scrapyard.parts.show', $part) }}" variant="secondary" size="md" class="w-full min-[390px]:w-auto">
                                        Voir la pièce
                                    </x-ui.button>

                                    <x-ui.button href="{{ route('scrapyard.parts.preparation.edit', $part) }}" variant="secondary" size="md" class="w-full min-[390px]:w-auto">
                                        Préparer / vérifier
                                    </x-ui.button>

                                    @if (! $part->is_published)
                                        <form method="POST" action="{{ route('scrapyard.parts.publish', $part) }}" class="min-[390px]:inline-flex">
                                            @csrf

                                            <x-ui.button as="button" type="submit" variant="primary" size="md" class="w-full min-[390px]:w-auto">
                                                Publier
                                            </x-ui.button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('scrapyard.parts.unpublish', $part) }}" class="min-[390px]:inline-flex">
                                            @csrf

                                            <x-ui.button as="button" type="submit" variant="secondary" size="md" class="w-full min-[390px]:w-auto">
                                                Retirer
                                            </x-ui.button>
                                        </form>
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
