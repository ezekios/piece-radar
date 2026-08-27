<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Tableau de bord casse - Pièce Radar</title>

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

            $statCards = [
                ['label' => 'Véhicules', 'value' => $stats['vehicles_total']],
                ['label' => 'Pièces', 'value' => $stats['parts_total']],
                ['label' => 'Pièces disponibles', 'value' => $stats['available_parts']],
                ['label' => 'Pièces mises de côté', 'value' => $stats['reserved_parts']],
                ['label' => 'Demandes reçues', 'value' => $stats['requests_total']],
                ['label' => 'En attente', 'value' => $stats['pending_requests']],
                ['label' => 'Acceptées', 'value' => $stats['accepted_requests']],
                ['label' => 'Refusées', 'value' => $stats['refused_requests']],
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-4xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                            Espace casse
                        </span>
                    </div>

                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Tableau de bord casse</h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $scrapyard?->name ?? 'Aucune casse disponible' }}
                                @if ($scrapyard?->city)
                                    · {{ $scrapyard->city }}
                                @endif
                            </p>
                        </div>

                        <a href="{{ route('scrapyard.requests.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                            Voir les demandes reçues
                        </a>
                    </div>
                </header>

                @if (! $scrapyard)
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune casse n’est disponible.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Le tableau de bord affichera les statistiques dès qu’une casse existera en base.
                        </p>
                    </section>
                @else
                    <section class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($statCards as $stat)
                            <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                                <p class="text-2xl font-black text-zinc-950">{{ $stat['value'] }}</p>
                                <p class="mt-1 text-xs font-bold leading-5 text-zinc-500">{{ $stat['label'] }}</p>
                            </article>
                        @endforeach
                    </section>

                    <section class="mt-6">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h2 class="text-lg font-black text-zinc-950">Dernières demandes</h2>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-500 ring-1 ring-zinc-200">
                                5 dernières
                            </span>
                        </div>

                        @if ($latestRequests->isEmpty())
                            <div class="rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                                <h3 class="text-base font-black text-zinc-950">Aucune demande reçue</h3>
                                <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                                    Les dernières demandes apparaîtront ici.
                                </p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($latestRequests as $holdRequest)
                                    @php
                                        $part = $holdRequest->part;
                                        $vehicle = $part?->vehicle;
                                        $status = $holdRequest->status;
                                    @endphp

                                    <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="truncate text-base font-black text-zinc-950">
                                                    {{ $part?->name ?? 'Pièce non renseignée' }}
                                                </h3>
                                                <p class="mt-1 truncate text-xs font-semibold text-zinc-700">
                                                    {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                                    @if ($vehicle?->year)
                                                        · {{ $vehicle->year }}
                                                    @endif
                                                </p>
                                                <p class="mt-1 truncate text-xs text-zinc-500">
                                                    {{ $holdRequest->user?->name ?? 'Client non renseigné' }}
                                                </p>
                                            </div>

                                            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                                {{ $statusLabels[$status] ?? $status }}
                                            </span>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3">
                                            <p class="text-xs font-medium text-zinc-400">
                                                Reçue le {{ $holdRequest->created_at?->format('d/m/Y à H:i') }}
                                            </p>
                                            <a href="{{ route('scrapyard.requests.show', $holdRequest) }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                                Voir la demande
                                            </a>
                                        </div>

                                        @if ($part?->status)
                                            <p class="mt-2 text-xs font-bold text-zinc-500">
                                                Pièce : {{ $partStatusLabels[$part->status] ?? $part->status }}
                                            </p>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif
            </div>
        </main>
    </body>
</html>
