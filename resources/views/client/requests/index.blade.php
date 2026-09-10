<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mes demandes - Pièce Radar</title>
        <x-ui.theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        @php
            $statusLabels = [
                'pending' => 'En attente',
                'accepted' => 'Acceptée',
                'refused' => 'Refusée',
                'cancelled' => 'Annulée',
                'completed' => 'Terminée',
                'expired' => 'Expirée',
            ];

            $statusClasses = [
                'pending' => 'bg-[#FC8505]/10 text-[#C96504] dark:text-orange-200',
                'accepted' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
                'refused' => 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300',
                'cancelled' => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400',
                'completed' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300',
                'expired' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
            ];

            $displayTimezone = config('app.display_timezone', 'UTC');
            $user = auth()->user();
            $buyerAccountRoute = $user?->role === 'professional'
                ? route('professional.account.show')
                : route('client.account.show');
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-24 pt-5 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4 dark:border-zinc-800">
                    <a href="{{ route('client.parts.index') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour vers les pièces
                    </a>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" theme-aware />
                            <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50 sm:text-3xl">
                                Mes demandes
                            </h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                                {{ $requests->count() }} demande{{ $requests->count() > 1 ? 's' : '' }}
                            </p>
                        </div>

                        <div class="hidden flex-wrap items-center gap-2 sm:flex">
                            <x-ui.theme-toggle />
                            <a href="{{ $buyerAccountRoute }}" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 dark:ring-[#FC8505]/30 hover:text-[#E87804]">
                                {{ $user?->role === 'professional' ? 'Espace pro' : 'Mon compte' }}
                            </a>

                            <a href="{{ route('client.saved-searches.index') }}" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 dark:ring-[#FC8505]/30 hover:text-[#E87804]">
                                Mes recherches
                            </a>

                            <a href="{{ route('notifications.index') }}" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 dark:ring-[#FC8505]/30 hover:text-[#E87804]">
                                Notifications
                            </a>

                            <span class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-bold text-zinc-600 dark:text-zinc-400 ring-1 ring-zinc-200 dark:ring-zinc-700">
                                {{ auth()->user()?->email }}
                            </span>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 dark:ring-[#FC8505]/30 hover:text-[#E87804]">
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                @if ($requests->isEmpty())
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white dark:border-[#FC8505]/30 dark:bg-zinc-900 p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Aucune demande trouvée pour le moment.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            Aucune demande n’est encore associée à votre compte.
                        </p>
                        <a href="{{ route('client.parts.index') }}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950">
                            Rechercher une pièce
                        </a>
                    </section>
                @else
                    <section class="mt-5 space-y-3">
                        @foreach ($requests as $holdRequest)
                            @php
                                $part = $holdRequest->part;
                                $vehicle = $part?->vehicle;
                                $scrapyard = $vehicle?->scrapyard;
                                $status = $holdRequest->status;
                                $createdAtDisplay = $holdRequest->created_at?->copy()->timezone($displayTimezone);
                                $handledAtDisplay = $holdRequest->handled_at?->copy()->timezone($displayTimezone);
                                $reservedUntilDisplay = $holdRequest->reserved_until?->copy()->timezone($displayTimezone);
                            @endphp

                            <article class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-3">
                                    <div class="min-w-0">
                                        <h2 class="truncate text-base font-black text-zinc-950 dark:text-zinc-50">
                                            {{ $part?->name ?? 'Pièce non renseignée' }}
                                        </h2>
                                        <p class="mt-1 truncate text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                            {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                            @if ($vehicle?->year)
                                                · {{ $vehicle->year }}
                                            @endif
                                        </p>
                                        <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $scrapyard?->name ?? 'Casse non renseignée' }}
                                        </p>
                                    </div>

                                    <span class="w-fit shrink-0 rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400' }}">
                                        {{ $statusLabels[$status] ?? $status }}
                                    </span>
                                </div>

                                <div class="mt-4 flex flex-col gap-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 p-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                                    <div>
                                        <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Date de la demande</p>
                                        <p class="mt-1 text-sm font-black text-zinc-950 dark:text-zinc-50">
                                            {{ $createdAtDisplay?->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>

                                    <p class="shrink-0 text-xl font-black text-[#FC8505]">
                                        @if ($part?->price !== null)
                                            {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                        @else
                                            Prix sur demande
                                        @endif
                                    </p>
                                </div>

                                @if ($holdRequest->customer_message)
                                    <div class="mt-3 rounded-xl border border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-3">
                                        <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Votre message</p>
                                        <p class="mt-1 text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $holdRequest->customer_message }}</p>
                                    </div>
                                @endif

                                @if ($holdRequest->handled_at || $holdRequest->reserved_until)
                                    <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                        @if ($holdRequest->handled_at)
                                            <div class="rounded-xl border border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-3">
                                                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Traitée le</p>
                                                <p class="mt-1 font-black text-zinc-950 dark:text-zinc-50">
                                                    {{ $handledAtDisplay->format('d/m/Y à H:i') }}
                                                </p>
                                            </div>
                                        @endif

                                        @if ($holdRequest->reserved_until)
                                            <div class="rounded-xl border border-orange-100 bg-[#FC8505]/5 dark:border-[#FC8505]/30 dark:bg-[#FC8505]/10 p-3">
                                                <p class="text-xs font-bold text-[#C96504] dark:text-orange-200">Réservée jusqu’au</p>
                                                <p class="mt-1 font-black text-zinc-950 dark:text-zinc-50">
                                                    {{ $reservedUntilDisplay->format('d/m/Y à H:i') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-3 flex justify-end border-t border-zinc-100 dark:border-zinc-800 pt-3">
                                    <a href="{{ route('client.requests.show', $holdRequest) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-[#FC8505]/30 bg-white dark:bg-zinc-900 px-4 py-2.5 text-sm font-black text-[#FC8505] transition hover:bg-[#FC8505]/10 focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950 sm:w-auto">
                                        Voir la demande
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>
        </main>

        <x-client.mobile-navigation active="requests" />
    </body>
</html>
