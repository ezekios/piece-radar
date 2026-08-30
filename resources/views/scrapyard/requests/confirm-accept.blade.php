<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Confirmer l’acceptation - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $part = $partHoldRequest->part;
            $vehicle = $part?->vehicle;
            $requestScrapyard = $vehicle?->scrapyard;

            $partStatusLabels = [
                'available' => 'Disponible',
                'preparing' => 'En préparation',
                'reserved' => 'Mise de côté',
                'sold' => 'Vendue',
                'unavailable' => 'Non disponible',
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
                <header class="border-b border-zinc-200/80 pb-4">
                    <div>
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        @include('scrapyard.partials.navigation')
                        <a href="{{ route('scrapyard.requests.show', $partHoldRequest) }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                            Retour vers la demande
                        </a>
                        <h1 class="mt-4 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Confirmer l’acceptation</h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            {{ $requestScrapyard?->name ?? $scrapyard?->name ?? 'Casse non renseignée' }}
                            @if ($requestScrapyard?->city ?? $scrapyard?->city)
                                · {{ $requestScrapyard?->city ?? $scrapyard->city }}
                            @endif
                        </p>
                    </div>
                </header>

                <div class="mt-4 space-y-3">
                    <section class="rounded-2xl border border-[#FC8505]/20 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Validation requise</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-zinc-700">
                            Vous êtes sur le point d’accepter cette demande.
                            En confirmant, vous certifiez que la pièce est toujours disponible et peut être mise de côté pour le client.
                            Après validation, les informations de contact nécessaires à la mise en relation pourront être débloquées.
                        </p>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Résumé de la demande</h2>

                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="font-medium text-zinc-500">Demande</dt>
                                <dd class="mt-1 font-black text-zinc-900">#{{ $partHoldRequest->id }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Statut</dt>
                                <dd class="mt-1 font-black text-zinc-900">En attente</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Reçue le</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $partHoldRequest->created_at?->format('d/m/Y à H:i') }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-base font-black text-zinc-950">Pièce demandée</h2>
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
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <a
                                href="{{ route('scrapyard.requests.show', $partHoldRequest) }}"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-5 py-4 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                            >
                                Annuler
                            </a>

                            <form method="POST" action="{{ route('scrapyard.requests.accept', $partHoldRequest) }}">
                                @csrf

                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                                >
                                    Confirmer l’acceptation
                                </button>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </body>
</html>
