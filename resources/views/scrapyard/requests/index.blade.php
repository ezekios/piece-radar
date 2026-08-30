<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Demandes reçues - Pièce Radar</title>

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

            $statusClasses = [
                'pending' => 'bg-[#FC8505]/10 text-[#C96504]',
                'accepted' => 'bg-emerald-50 text-emerald-700',
                'refused' => 'bg-red-50 text-red-700',
                'cancelled' => 'bg-zinc-100 text-zinc-600',
                'completed' => 'bg-blue-50 text-blue-700',
            ];

            $statusTreatmentLabels = [
                'pending' => 'En attente de traitement',
                'accepted' => 'Demande acceptée',
                'refused' => 'Demande refusée',
                'cancelled' => 'Demande annulée',
                'completed' => 'Demande terminée',
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                            Espace casse
                        </span>
                    </div>

                    @include('scrapyard.partials.navigation')

                    <h1 class="mt-4 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Demandes reçues</h1>
                    <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                        {{ $requests->count() }} demande{{ $requests->count() > 1 ? 's' : '' }} reçue{{ $requests->count() > 1 ? 's' : '' }}
                    </p>
                </header>

                <section class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase text-[#FC8505]">Casse automobile</p>
                            <h2 class="mt-1 truncate text-lg font-black text-zinc-950">
                                {{ $scrapyard?->name ?? 'Aucune casse trouvée' }}
                            </h2>
                            <p class="mt-1 text-sm font-medium text-zinc-500">
                                {{ $scrapyard?->city ?? 'Ville non renseignée' }}
                            </p>
                        </div>

                        <div class="shrink-0 rounded-2xl bg-[#FC8505]/10 px-4 py-3 text-center">
                            <p class="text-2xl font-black text-[#FC8505]">{{ $pendingRequestsCount }}</p>
                            <p class="text-xs font-black text-[#C96504]">En attente</p>
                        </div>
                    </div>
                </section>

                @if ($requests->isEmpty())
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune demande reçue</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Les demandes de mise de côté apparaîtront ici.
                        </p>
                    </section>
                @else
                    <section class="mt-5 space-y-3">
                        @foreach ($requests as $holdRequest)
                            @php
                                $part = $holdRequest->part;
                                $vehicle = $part?->vehicle;
                                $requestScrapyard = $vehicle?->scrapyard;
                                $status = $holdRequest->status;
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
                                            {{ $requestScrapyard?->name ?? $scrapyard?->name ?? 'Casse non renseignée' }}
                                            @if ($requestScrapyard?->city)
                                                · {{ $requestScrapyard->city }}
                                            @endif
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                        {{ $statusLabels[$status] ?? $status }}
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs font-bold text-zinc-500">Client</p>
                                        <p class="mt-1 font-black text-zinc-950">{{ $holdRequest->user?->name ?? 'Non renseigné' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-bold text-zinc-500">Téléphone</p>
                                        <p class="mt-1 font-black text-zinc-950">{{ $holdRequest->user?->phone ?? 'Non renseigné' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-bold text-zinc-500">Email</p>
                                        <p class="mt-1 break-words font-black text-zinc-950">{{ $holdRequest->user?->email ?? 'Non renseigné' }}</p>
                                    </div>
                                </div>

                                @if ($holdRequest->customer_message)
                                    <div class="mt-3 rounded-xl border border-zinc-100 bg-white p-3">
                                        <p class="text-xs font-bold text-zinc-500">Message du client</p>
                                        <p class="mt-1 text-sm leading-6 text-zinc-700">{{ $holdRequest->customer_message }}</p>
                                    </div>
                                @endif

                                <div class="mt-3 border-t border-zinc-100 pt-3">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-xs font-medium text-zinc-400">
                                            Reçue le {{ $holdRequest->created_at?->format('d/m/Y à H:i') }}
                                        </p>

                                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                            {{ $statusTreatmentLabels[$status] ?? $status }}
                                        </span>
                                    </div>

                                    <div class="mt-3">
                                        <p class="text-xs font-black uppercase text-zinc-500">Actions rapides</p>

                                        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                                            <a href="{{ route('scrapyard.requests.show', $holdRequest) }}" class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-[#FC8505]/30 bg-white px-4 text-sm font-black text-[#FC8505] transition hover:bg-[#FC8505]/10 focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                                Voir la demande
                                            </a>

                                            @if ($status === 'pending')
                                                <form method="POST" action="{{ route('scrapyard.requests.accept', $holdRequest) }}" class="sm:inline-flex">
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-[#FC8505] px-4 text-sm font-black text-white transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto"
                                                    >
                                                        Accepter
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('scrapyard.requests.refuse', $holdRequest) }}" class="sm:inline-flex">
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto"
                                                    >
                                                        Refuser
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>
        </main>
    </body>
</html>
