<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Détail de ma demande - Pièce Radar</title>
        <x-ui.theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        @php
            $part = $partHoldRequest->part;
            $vehicle = $part?->vehicle;
            $scrapyard = $vehicle?->scrapyard;
            $displayTimezone = config('app.display_timezone', 'UTC');
            $createdAtDisplay = $partHoldRequest->created_at?->copy()->timezone($displayTimezone);
            $handledAtDisplay = $partHoldRequest->handled_at?->copy()->timezone($displayTimezone);
            $reservedUntilDisplay = $partHoldRequest->reserved_until?->copy()->timezone($displayTimezone);
            $user = auth()->user();
            $buyerAccountRoute = $user?->role === 'professional'
                ? route('professional.account.show')
                : route('client.account.show');

            $statusLabels = [
                'pending' => 'En attente',
                'accepted' => 'Acceptée',
                'refused' => 'Refusée',
                'cancelled' => 'Annulée',
                'completed' => 'Terminée',
                'expired' => 'Expirée',
            ];

            $statusHelpTexts = [
                'pending' => 'Votre demande est en attente de traitement par la casse.',
                'accepted' => 'Votre demande a été acceptée. La pièce est mise de côté temporairement.',
                'refused' => 'Votre demande a été refusée par la casse.',
                'completed' => 'Cette demande est terminée.',
                'cancelled' => 'Cette demande a été annulée.',
                'expired' => 'Votre réservation a expiré.',
            ];

            $statusClasses = [
                'pending' => 'bg-[#FC8505]/10 text-[#C96504] dark:text-orange-200',
                'accepted' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
                'refused' => 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300',
                'cancelled' => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400',
                'completed' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300',
                'expired' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
            ];

            $partStatusLabels = [
                'available' => 'Disponible',
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

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-24 pt-5 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4 dark:border-zinc-800">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <a href="{{ route('client.requests.index') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                            Retour vers mes demandes
                        </a>

                        <div class="hidden flex-wrap items-center gap-2 sm:flex">
                            <x-ui.theme-toggle />
                            <a href="{{ $buyerAccountRoute }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                {{ $user?->role === 'professional' ? 'Espace pro' : 'Mon compte' }}
                            </a>

                            <a href="{{ route('client.saved-searches.index') }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                Mes recherches
                            </a>

                            <a href="{{ route('notifications.index') }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                Notifications
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" theme-aware />
                            <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50 sm:text-3xl">
                                Détail de ma demande
                            </h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                                Demande envoyée le {{ $createdAtDisplay?->format('d/m/Y à H:i') }}
                            </p>
                        </div>

                        <span class="w-fit rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$partHoldRequest->status] ?? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400' }}">
                            {{ $statusLabels[$partHoldRequest->status] ?? $partHoldRequest->status }}
                        </span>
                    </div>
                </header>

                <section class="mt-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                    <p class="text-sm font-bold leading-6 text-zinc-700 dark:text-zinc-300">
                        {{ $statusHelpTexts[$partHoldRequest->status] ?? 'Votre demande est en cours de suivi.' }}
                    </p>

                    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800 p-3">
                            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Date de la demande</p>
                            <p class="mt-1 font-black text-zinc-950 dark:text-zinc-50">{{ $createdAtDisplay?->format('d/m/Y à H:i') }}</p>
                        </div>

                        @if ($partHoldRequest->handled_at)
                            <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800 p-3">
                                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Date de traitement</p>
                                <p class="mt-1 font-black text-zinc-950 dark:text-zinc-50">{{ $handledAtDisplay->format('d/m/Y à H:i') }}</p>
                            </div>
                        @endif

                        @if ($partHoldRequest->reserved_until)
                            <div class="rounded-xl border border-orange-100 bg-[#FC8505]/5 dark:border-[#FC8505]/30 dark:bg-[#FC8505]/10 p-3">
                                <p class="text-xs font-bold text-[#C96504] dark:text-orange-200">Réservation limite</p>
                                <p class="mt-1 font-black text-zinc-950 dark:text-zinc-50">{{ $reservedUntilDisplay->format('d/m/Y à H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </section>

                <div class="mt-3 space-y-3">
                    <section class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Pièce demandée</h2>
                                <p class="mt-2 text-lg font-black text-zinc-950 dark:text-zinc-50">{{ $part?->name ?? 'Pièce non renseignée' }}</p>
                                <p class="mt-1 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    {{ $conditionLabels[$part?->condition] ?? $part?->condition ?? 'État non précisé' }}
                                </p>
                            </div>

                            <div class="text-left sm:text-right">
                                <p class="text-2xl font-black text-[#FC8505]">
                                    @if ($part?->price !== null)
                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                    @else
                                        Prix sur demande
                                    @endif
                                </p>
                                <p class="mt-1 text-xs font-black text-zinc-500 dark:text-zinc-400">
                                    Statut pièce : {{ $partStatusLabels[$part?->status] ?? $part?->status ?? 'Non renseigné' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Véhicule associé</h2>

                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Marque</dt>
                                <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ $vehicle?->brand ?? 'Non renseignée' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Modèle</dt>
                                <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ $vehicle?->model ?? 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Année</dt>
                                <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ $vehicle?->year ?? 'Non renseignée' }}</dd>
                            </div>

                            @if ($vehicle?->engine)
                                <div>
                                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Motorisation</dt>
                                    <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ $vehicle->engine }}</dd>
                                </div>
                            @endif

                            @if ($vehicle?->fuel)
                                <div>
                                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Carburant</dt>
                                    <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ $vehicle->fuel }}</dd>
                                </div>
                            @endif

                            @if ($vehicle?->mileage)
                                <div>
                                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Kilométrage</dt>
                                    <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ number_format($vehicle->mileage, 0, ',', ' ') }} km</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Casse automobile</h2>

                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Nom</dt>
                                <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ $scrapyard?->name ?? 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Ville</dt>
                                <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ $scrapyard?->city ?? 'Non renseignée' }}</dd>
                            </div>

                            @if ($scrapyard?->phone)
                                <div>
                                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Téléphone</dt>
                                    <dd class="mt-1 font-black text-zinc-900 dark:text-zinc-100">{{ $scrapyard->phone }}</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    @if ($partHoldRequest->customer_message)
                        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                            <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Message envoyé</h2>
                            <p class="mt-3 text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $partHoldRequest->customer_message }}</p>
                        </section>
                    @endif
                </div>
            </div>
        </main>

        <x-client.mobile-navigation active="requests" />
    </body>
</html>
