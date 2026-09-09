<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mes recherches - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $statusLabels = [
                'active' => 'Recherche en cours',
                'matched' => 'Correspondance trouvée',
                'closed' => 'Recherche fermée',
            ];

            $statusClasses = [
                'active' => 'bg-[#FC8505]/10 text-[#C96504]',
                'matched' => 'bg-emerald-50 text-emerald-700',
                'closed' => 'bg-zinc-100 text-zinc-600',
            ];

            $displayTimezone = config('app.display_timezone', 'UTC');
            $user = auth()->user();
            $buyerAccountRoute = $user?->role === 'professional'
                ? route('professional.account.show')
                : route('client.account.show');
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-24 pt-5 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <a href="{{ route('client.parts.index') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour vers les pièces
                    </a>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />
                            <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                                Mes recherches
                            </h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $savedSearches->count() }} recherche{{ $savedSearches->count() > 1 ? 's' : '' }} enregistrée{{ $savedSearches->count() > 1 ? 's' : '' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('client.requests.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                Mes demandes
                            </a>

                            <a href="{{ $buyerAccountRoute }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                {{ $user?->role === 'professional' ? 'Espace pro' : 'Mon compte' }}
                            </a>

                            <a href="{{ route('notifications.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                Notifications
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-full bg-white px-3 py-1 text-xs font-black text-zinc-600 ring-1 ring-zinc-200 hover:text-zinc-900">
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                @if (session('success'))
                    <div class="mt-4 rounded-2xl border border-orange-200 bg-white p-4 text-sm font-bold text-[#C96504] shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($savedSearches->isEmpty())
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune recherche enregistrée.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Lancez une recherche de pièce. Si aucun résultat n’est disponible, vous pourrez enregistrer votre besoin.
                        </p>
                        <a href="{{ route('client.parts.index') }}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                            Rechercher une pièce
                        </a>
                    </section>
                @else
                    <section class="mt-5 space-y-3">
                        @foreach ($savedSearches as $savedSearch)
                            @php
                                $matchedPart = $savedSearch->matchedPart;
                                $matchedVehicle = $matchedPart?->vehicle;
                                $matchedScrapyard = $matchedVehicle?->scrapyard;
                                $createdAtDisplay = $savedSearch->created_at?->copy()->timezone($displayTimezone);
                                $matchedAtDisplay = $savedSearch->matched_at?->copy()->timezone($displayTimezone);
                                $isMatchedPartVisible = $matchedPart?->is_published && $matchedPart?->status === 'available';
                            @endphp

                            <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="text-base font-black text-zinc-950">
                                            {{ $savedSearch->part_name }}
                                        </h2>
                                        <p class="mt-1 text-sm font-semibold text-zinc-700">
                                            {{ $savedSearch->vehicle_brand }} {{ $savedSearch->vehicle_model }}
                                            @if ($savedSearch->vehicle_year)
                                                · {{ $savedSearch->vehicle_year }}
                                            @endif
                                        </p>
                                        @if ($savedSearch->part_category)
                                            <p class="mt-1 text-xs text-zinc-500">{{ $savedSearch->part_category }}</p>
                                        @endif
                                    </div>

                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$savedSearch->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                        {{ $statusLabels[$savedSearch->status] ?? $savedSearch->status }}
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                    <div class="rounded-xl bg-zinc-50 p-3">
                                        <p class="text-xs font-bold text-zinc-500">Date de création</p>
                                        <p class="mt-1 font-black text-zinc-950">{{ $createdAtDisplay?->format('d/m/Y à H:i') }}</p>
                                    </div>

                                    @if ($matchedAtDisplay)
                                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3">
                                            <p class="text-xs font-bold text-emerald-700">Correspondance détectée</p>
                                            <p class="mt-1 font-black text-zinc-950">{{ $matchedAtDisplay->format('d/m/Y à H:i') }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if ($matchedPart)
                                    <div class="mt-3 rounded-xl border border-zinc-100 bg-zinc-50 p-3">
                                        <p class="text-xs font-bold text-zinc-500">Pièce trouvée</p>
                                        <p class="mt-1 text-sm font-black text-zinc-950">{{ $matchedPart->name }}</p>
                                        <p class="mt-1 text-xs text-zinc-600">
                                            {{ $matchedVehicle?->brand }} {{ $matchedVehicle?->model }}
                                            @if ($matchedScrapyard?->name)
                                                · {{ $matchedScrapyard->name }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                <div class="mt-3 flex flex-col gap-2 border-t border-zinc-100 pt-3 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-xs font-medium text-zinc-500">
                                        @if ($savedSearch->status === 'active')
                                            Pièce Radar surveille les arrivées compatibles.
                                        @elseif ($isMatchedPartVisible)
                                            Vous pouvez consulter cette pièce et faire une demande de mise de côté.
                                        @else
                                            La pièce associée n’est plus disponible côté client.
                                        @endif
                                    </p>

                                    @if ($isMatchedPartVisible)
                                        <a href="{{ route('pieces.show', $matchedPart) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-4 py-2 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                            Voir la pièce
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>
        </main>

        <x-client.mobile-navigation active="search" />
    </body>
</html>
