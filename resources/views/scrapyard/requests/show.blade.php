<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Détail de la demande - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $part = $partHoldRequest->part;
            $vehicle = $part?->vehicle;
            $requestScrapyard = $vehicle?->scrapyard;
            $client = $partHoldRequest->user;
            $canShowClientContact = in_array($partHoldRequest->status, ['accepted', 'completed'], true);

            $statusLabels = [
                'pending' => 'En attente',
                'accepted' => 'Acceptée',
                'refused' => 'Refusée',
                'cancelled' => 'Annulée',
                'completed' => 'Terminée',
            ];

            $partStatusLabels = [
                'available' => 'Disponible',
                'reserved' => 'Mise de côté',
                'sold' => 'Vendue',
                'unavailable' => 'Non disponible',
            ];

            $statusClasses = [
                'pending' => 'bg-[#FC8505]/10 text-[#C96504]',
                'accepted' => 'bg-emerald-50 text-emerald-700',
                'refused' => 'bg-red-50 text-red-700',
                'cancelled' => 'bg-zinc-100 text-zinc-600',
                'completed' => 'bg-blue-50 text-blue-700',
            ];

            $conditionLabels = [
                'unknown' => 'État non précisé',
                'used_good' => 'Occasion bon état',
                'used_average' => 'Occasion état moyen',
                'damaged' => 'Endommagée',
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                @if (session('success'))
                    <div class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700 shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm font-bold text-red-700 shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                            @include('scrapyard.partials.navigation')
                            <a href="{{ route('scrapyard.requests.index') }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                Retour vers les demandes
                            </a>
                            <h1 class="mt-4 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                                Détail de la demande
                            </h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                Reçue le {{ $partHoldRequest->created_at?->format('d/m/Y à H:i') }}
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$partHoldRequest->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                            {{ $statusLabels[$partHoldRequest->status] ?? $partHoldRequest->status }}
                        </span>
                    </div>
                </header>

                <div class="mt-4 space-y-3">
                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Client</h2>

                        @if ($canShowClientContact)
                            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                                <div>
                                    <dt class="font-medium text-zinc-500">Nom</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $client?->name ?? 'Non renseigné' }}</dd>
                                </div>

                                <div>
                                    <dt class="font-medium text-zinc-500">Téléphone</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $client?->phone ?? 'Non renseigné' }}</dd>
                                </div>

                                <div>
                                    <dt class="font-medium text-zinc-500">Email</dt>
                                    <dd class="mt-1 break-words font-black text-zinc-900">{{ $client?->email ?? 'Non renseigné' }}</dd>
                                </div>
                            </dl>
                        @else
                            <div class="mt-3 rounded-xl bg-zinc-50 p-3">
                                <p class="text-sm font-medium leading-6 text-zinc-600">
                                    Les coordonnées du client seront disponibles après acceptation de la demande.
                                </p>
                            </div>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-base font-black text-zinc-950">Pièce</h2>
                                <p class="mt-2 text-lg font-black text-zinc-950">{{ $part?->name ?? 'Pièce non renseignée' }}</p>
                                <p class="mt-1 text-sm font-medium text-zinc-600">
                                    {{ $conditionLabels[$part?->condition] ?? $part?->condition ?? 'État non précisé' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-2xl font-black text-[#FC8505]">
                                    @if ($part?->price !== null)
                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                    @else
                                        Prix sur demande
                                    @endif
                                </p>
                                <p class="mt-1 text-xs font-black text-zinc-500">
                                    Statut pièce : {{ $partStatusLabels[$part?->status] ?? $part?->status ?? 'Non renseigné' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    @if ($partHoldRequest->status === 'pending')
                        <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                            <h2 class="text-base font-black text-zinc-950">Traiter la demande</h2>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <a
                                    href="{{ route('scrapyard.requests.accept.confirm', $partHoldRequest) }}"
                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                                >
                                    Accepter la demande
                                </a>

                                <form method="POST" action="{{ route('scrapyard.requests.refuse', $partHoldRequest) }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-5 py-4 text-sm font-black text-zinc-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-200 focus:ring-offset-2"
                                    >
                                        Refuser la demande
                                    </button>
                                </form>
                            </div>
                        </section>
                    @endif

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Véhicule associé</h2>

                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="font-medium text-zinc-500">Marque</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->brand ?? 'Non renseignée' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Modèle</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->model ?? 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Année</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->year ?? 'Non renseignée' }}</dd>
                            </div>

                            @if ($vehicle?->engine)
                                <div>
                                    <dt class="font-medium text-zinc-500">Motorisation</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->engine }}</dd>
                                </div>
                            @endif

                            @if ($vehicle?->fuel)
                                <div>
                                    <dt class="font-medium text-zinc-500">Carburant</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->fuel }}</dd>
                                </div>
                            @endif

                            @if ($vehicle?->mileage)
                                <div>
                                    <dt class="font-medium text-zinc-500">Kilométrage</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ number_format($vehicle->mileage, 0, ',', ' ') }} km</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Casse automobile</h2>

                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="font-medium text-zinc-500">Nom</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $requestScrapyard?->name ?? $scrapyard?->name ?? 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Ville</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $requestScrapyard?->city ?? $scrapyard?->city ?? 'Non renseignée' }}</dd>
                            </div>
                        </dl>
                    </section>

                    @if ($partHoldRequest->customer_message)
                        <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                            <h2 class="text-base font-black text-zinc-950">Message du client</h2>
                            <p class="mt-3 text-sm leading-6 text-zinc-700">{{ $partHoldRequest->customer_message }}</p>
                        </section>
                    @endif
                </div>
            </div>
        </main>
    </body>
</html>
