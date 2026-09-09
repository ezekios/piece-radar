@php
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

    $statusTreatmentLabels = [
        'pending' => 'En attente de traitement',
        'accepted' => 'Demande acceptée',
        'refused' => 'Demande refusée',
        'cancelled' => 'Demande annulée',
        'completed' => 'Demande terminée',
        'expired' => 'Réservation expirée',
    ];

    $filterItems = [
        ['label' => 'Toutes', 'status' => null],
        ['label' => 'En attente', 'status' => 'pending'],
        ['label' => 'Acceptées', 'status' => 'accepted'],
        ['label' => 'Refusées', 'status' => 'refused'],
        ['label' => 'Annulées', 'status' => 'cancelled'],
        ['label' => 'Terminées', 'status' => 'completed'],
        ['label' => 'Expirées', 'status' => 'expired'],
    ];

    $displayTimezone = config('app.display_timezone', 'UTC');
@endphp

<x-layouts.scrapyard title="Demandes reçues - Pièce Radar" max-width="max-w-6xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Demandes"
            title="Demandes reçues"
            description="{{ $requests->count() }} demande{{ $requests->count() > 1 ? 's' : '' }} reçue{{ $requests->count() > 1 ? 's' : '' }}"
        >
            <x-slot:actions>
                <x-ui.badge variant="orange">
                    {{ $pendingRequestsCount }} en attente
                </x-ui.badge>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot:header>

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,0.28fr)_minmax(0,0.72fr)]">
        <aside class="space-y-5">
            <x-ui.card as="section" padding="p-4 sm:p-5">
                <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Casse automobile</p>
                <h2 class="mt-2 break-words text-lg font-black text-zinc-950">
                    {{ $scrapyard?->name ?? 'Aucune casse trouvée' }}
                </h2>
                <p class="mt-1 text-sm font-medium text-zinc-500">
                    {{ $scrapyard?->city ?? 'Ville non renseignée' }}
                </p>

                <div class="mt-4 rounded-2xl bg-[#FC8505]/10 px-4 py-3 text-center">
                    <p class="text-3xl font-black text-[#FC8505]">{{ $pendingRequestsCount }}</p>
                    <p class="text-xs font-black text-[#C96504]">Demandes en attente</p>
                </div>
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Filtres</p>

                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($filterItems as $filter)
                        @php
                            $isActive = $activeStatus === $filter['status'];
                            $filterUrl = $filter['status']
                                ? route('scrapyard.requests.index', ['status' => $filter['status']])
                                : route('scrapyard.requests.index');
                        @endphp

                        <a
                            href="{{ $filterUrl }}"
                            @if ($isActive) aria-current="page" @endif
                            class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border px-3 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 {{ $isActive ? 'border-[#FC8505] bg-[#FC8505] text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-orange-200 hover:text-[#FC8505]' }}"
                        >
                            {{ $filter['label'] }}
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        </aside>

        <section>
            @if ($requests->isEmpty())
                <x-ui.empty-state
                    title="Aucune demande reçue"
                    description="Les demandes de mise de côté apparaîtront ici."
                />
            @else
                <div class="space-y-3">
                    @foreach ($requests as $holdRequest)
                        @php
                            $part = $holdRequest->part;
                            $vehicle = $part?->vehicle;
                            $requestScrapyard = $vehicle?->scrapyard;
                            $status = $holdRequest->status;
                            $canShowClientContact = in_array($status, ['accepted', 'completed'], true);
                            $createdAtDisplay = $holdRequest->created_at?->copy()->timezone($displayTimezone);
                        @endphp

                        <x-ui.card as="article" padding="p-4 sm:p-5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="break-words text-lg font-black text-zinc-950">
                                            {{ $part?->name ?? 'Pièce non renseignée' }}
                                        </h2>
                                        <x-ui.badge :variant="$statusVariants[$status] ?? 'neutral'">
                                            {{ $statusLabels[$status] ?? $status }}
                                        </x-ui.badge>
                                    </div>

                                    <p class="mt-1 text-sm font-semibold text-zinc-700">
                                        {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                        @if ($vehicle?->year)
                                            · {{ $vehicle->year }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs font-medium text-zinc-500">
                                        {{ $requestScrapyard?->name ?? $scrapyard?->name ?? 'Casse non renseignée' }}
                                        @if ($requestScrapyard?->city)
                                            · {{ $requestScrapyard->city }}
                                        @endif
                                    </p>
                                </div>

                                <p class="text-xs font-black text-zinc-400 lg:text-right">
                                    Reçue le {{ $createdAtDisplay?->format('d/m/Y à H:i') }}
                                </p>
                            </div>

                            <dl class="mt-4 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm min-[390px]:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <dt class="text-xs font-bold text-zinc-500">Client</dt>
                                    <dd class="mt-1 font-black text-zinc-950">
                                        {{ $canShowClientContact ? ($holdRequest->user?->name ?? 'Non renseigné') : 'Demande client' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-bold text-zinc-500">Téléphone</dt>
                                    <dd class="mt-1 font-black text-zinc-950">
                                        {{ $canShowClientContact ? ($holdRequest->user?->phone ?? 'Non renseigné') : 'Masqué avant acceptation' }}
                                    </dd>
                                </div>

                                <div class="min-[390px]:col-span-2 lg:col-span-1">
                                    <dt class="text-xs font-bold text-zinc-500">Email</dt>
                                    <dd class="mt-1 break-words font-black text-zinc-950">
                                        {{ $canShowClientContact ? ($holdRequest->user?->email ?? 'Non renseigné') : 'Masqué avant acceptation' }}
                                    </dd>
                                </div>
                            </dl>

                            @if ($holdRequest->customer_message)
                                <div class="mt-3 rounded-xl border border-zinc-100 bg-white p-3">
                                    <p class="text-xs font-bold text-zinc-500">Message du client</p>
                                    <p class="mt-1 text-sm leading-6 text-zinc-700">{{ $holdRequest->customer_message }}</p>
                                </div>
                            @endif

                            <div class="mt-4 border-t border-zinc-100 pt-4">
                                <div class="flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-center min-[390px]:justify-between">
                                    <x-ui.badge :variant="$statusVariants[$status] ?? 'neutral'">
                                        {{ $statusTreatmentLabels[$status] ?? $status }}
                                    </x-ui.badge>

                                    <div class="flex flex-col gap-2 min-[390px]:flex-row min-[390px]:flex-wrap min-[390px]:justify-end">
                                        <x-ui.button href="{{ route('scrapyard.requests.show', $holdRequest) }}" variant="secondary" class="w-full min-[390px]:w-auto">
                                            Voir la demande
                                        </x-ui.button>

                                        @if ($status === 'pending')
                                            <x-ui.button href="{{ route('scrapyard.requests.accept.confirm', $holdRequest) }}" variant="primary" class="w-full min-[390px]:w-auto">
                                                Accepter
                                            </x-ui.button>

                                            <form method="POST" action="{{ route('scrapyard.requests.refuse', $holdRequest) }}" class="min-[390px]:inline-flex">
                                                @csrf

                                                <x-ui.button as="button" type="submit" variant="danger" class="w-full min-[390px]:w-auto">
                                                    Refuser
                                                </x-ui.button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.scrapyard>
