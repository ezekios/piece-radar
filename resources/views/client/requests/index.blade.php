<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mes demandes - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
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
                'pending' => 'bg-[#FC8505]/10 text-[#C96504]',
                'accepted' => 'bg-emerald-50 text-emerald-700',
                'refused' => 'bg-red-50 text-red-700',
                'cancelled' => 'bg-zinc-100 text-zinc-600',
                'completed' => 'bg-blue-50 text-blue-700',
                'expired' => 'bg-amber-50 text-amber-700',
            ];

            $displayTimezone = config('app.display_timezone', 'UTC');
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <a href="{{ route('client.parts.index') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour vers les pièces
                    </a>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                            <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                                Mes demandes
                            </h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $requests->count() }} demande{{ $requests->count() > 1 ? 's' : '' }}
                            </p>
                        </div>

                        @if ($clientEmail)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                                {{ $clientEmail }}
                            </span>
                        @endif
                    </div>
                </header>

                @if (! $clientEmail)
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune demande trouvée pour le moment.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Envoyez une demande de mise de côté pour pouvoir la suivre ici.
                        </p>
                        <a href="{{ route('client.parts.index') }}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                            Rechercher une pièce
                        </a>
                    </section>
                @elseif ($requests->isEmpty())
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune demande trouvée pour le moment.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Aucune demande n’est associée à cette adresse email.
                        </p>
                        <a href="{{ route('client.parts.index') }}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
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
                                $handledAtDisplay = $holdRequest->handled_at?->copy()->timezone($displayTimezone);
                                $reservedUntilDisplay = $holdRequest->reserved_until?->copy()->timezone($displayTimezone);
                            @endphp

                            <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="truncate text-base font-black text-zinc-950">
                                            {{ $part?->name ?? 'Pièce non renseignée' }}
                                        </h2>
                                        <p class="mt-1 truncate text-xs font-semibold text-zinc-700">
                                            {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                            @if ($vehicle?->year)
                                                · {{ $vehicle->year }}
                                            @endif
                                        </p>
                                        <p class="mt-1 truncate text-xs text-zinc-500">
                                            {{ $scrapyard?->name ?? 'Casse non renseignée' }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                        {{ $statusLabels[$status] ?? $status }}
                                    </span>
                                </div>

                                <div class="mt-4 flex items-end justify-between gap-4 rounded-xl bg-zinc-50 p-3">
                                    <div>
                                        <p class="text-xs font-bold text-zinc-500">Date de la demande</p>
                                        <p class="mt-1 text-sm font-black text-zinc-950">
                                            {{ $holdRequest->created_at?->format('d/m/Y à H:i') }}
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
                                    <div class="mt-3 rounded-xl border border-zinc-100 bg-white p-3">
                                        <p class="text-xs font-bold text-zinc-500">Votre message</p>
                                        <p class="mt-1 text-sm leading-6 text-zinc-700">{{ $holdRequest->customer_message }}</p>
                                    </div>
                                @endif

                                @if ($holdRequest->handled_at || $holdRequest->reserved_until)
                                    <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                        @if ($holdRequest->handled_at)
                                            <div class="rounded-xl border border-zinc-100 bg-white p-3">
                                                <p class="text-xs font-bold text-zinc-500">Traitée le</p>
                                                <p class="mt-1 font-black text-zinc-950">
                                                    {{ $handledAtDisplay->format('d/m/Y à H:i') }}
                                                </p>
                                            </div>
                                        @endif

                                        @if ($holdRequest->reserved_until)
                                            <div class="rounded-xl border border-orange-100 bg-[#FC8505]/5 p-3">
                                                <p class="text-xs font-bold text-[#C96504]">Réservée jusqu’au</p>
                                                <p class="mt-1 font-black text-zinc-950">
                                                    {{ $reservedUntilDisplay->format('d/m/Y à H:i') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-3 flex justify-end border-t border-zinc-100 pt-3">
                                    <a href="{{ route('client.requests.show', $holdRequest) }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                        Voir la demande
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>
        </main>
    </body>
</html>
