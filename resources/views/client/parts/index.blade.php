<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Résultats de recherche - Pièce Radar</title>
        <x-ui.theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        @php
            $user = auth()->user();
            $isBuyer = in_array($user?->role, ['client', 'professional'], true);
            $buyerAccountRoute = $user?->role === 'professional'
                ? route('professional.account.show')
                : route('client.account.show');
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-24 pt-5 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4 dark:border-zinc-800">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <x-brand-logo :href="route('home')" image-class="h-9 w-auto max-w-[145px] object-contain" theme-aware />
                        <div class="hidden flex-wrap items-center gap-2 sm:flex">
                            <x-ui.theme-toggle />
                            @auth
                                @if ($isBuyer)
                                    <a href="{{ $buyerAccountRoute }}" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 dark:ring-[#FC8505]/30 hover:text-[#E87804]">
                                        {{ $user?->role === 'professional' ? 'Espace pro' : 'Mon compte' }}
                                    </a>

                                    <a href="{{ route('client.saved-searches.index') }}" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 dark:ring-[#FC8505]/30 hover:text-[#E87804]">
                                        Mes recherches
                                    </a>

                                    <a href="{{ route('client.requests.index') }}" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 dark:ring-[#FC8505]/30 hover:text-[#E87804]">
                                        Mes demandes
                                    </a>

                                    <a href="{{ route('notifications.index') }}" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 dark:ring-[#FC8505]/30 hover:text-[#E87804]">
                                        Notifications
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-black text-zinc-600 dark:text-zinc-400 ring-1 ring-zinc-200 dark:ring-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100">
                                            Déconnexion
                                        </button>
                                    </form>
                                @endif
                            @endauth

                            <span class="rounded-full bg-white dark:bg-zinc-900 px-3 py-1 text-xs font-bold text-zinc-600 dark:text-zinc-400 ring-1 ring-zinc-200 dark:ring-zinc-700">
                                Martinique
                            </span>
                        </div>
                    </div>

                    <h1 class="text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50 sm:text-3xl">Résultats de recherche</h1>
                    <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                        {{ $parts->count() }} pièces disponibles autour de vous
                    </p>
                </header>

                @if (session('success'))
                    <div class="mt-4 rounded-2xl border border-orange-200 bg-white dark:border-[#FC8505]/30 dark:bg-zinc-900 p-4 text-sm font-bold text-[#C96504] dark:text-orange-200 shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('client.parts.index') }}" class="mt-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-3 shadow-sm">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="q" class="text-xs font-black text-zinc-700 dark:text-zinc-300">Rechercher une pièce</label>
                            <input
                                id="q"
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="Ex. phare, alternateur, référence"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="license_plate" class="text-xs font-black text-zinc-700 dark:text-zinc-300">Plaque d’immatriculation</label>
                            <input
                                id="license_plate"
                                name="license_plate"
                                type="text"
                                value="{{ request('license_plate') }}"
                                placeholder="Ex. AB-123-CD"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium uppercase text-zinc-700 dark:text-zinc-300 placeholder:normal-case placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div>
                            <label for="category" class="text-xs font-black text-zinc-700 dark:text-zinc-300">Catégorie</label>
                            <input
                                id="category"
                                name="category"
                                type="text"
                                value="{{ request('category') }}"
                                placeholder="Optique, moteur..."
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div>
                            <label for="brand" class="text-xs font-black text-zinc-700 dark:text-zinc-300">Marque</label>
                            <input
                                id="brand"
                                name="brand"
                                type="text"
                                value="{{ request('brand') }}"
                                placeholder="Renault"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div>
                            <label for="model" class="text-xs font-black text-zinc-700 dark:text-zinc-300">Modèle</label>
                            <input
                                id="model"
                                name="model"
                                type="text"
                                value="{{ request('model') }}"
                                placeholder="Clio IV"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div>
                            <label for="city" class="text-xs font-black text-zinc-700 dark:text-zinc-300">Ville</label>
                            <input
                                id="city"
                                name="city"
                                type="text"
                                value="{{ request('city') }}"
                                placeholder="Fort-de-France"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>
                    </div>

                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <button
                            type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-xl bg-[#FC8505] px-5 text-sm font-black text-white transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950"
                        >
                            Rechercher
                        </button>

                        <a href="{{ route('client.parts.index') }}" class="inline-flex h-11 items-center justify-center text-sm font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200">
                            Réinitialiser
                        </a>
                    </div>
                </form>

                @if ($hasLicensePlate)
                    <section class="mt-3 rounded-2xl border border-orange-100 bg-[#FC8505]/5 dark:border-[#FC8505]/30 dark:bg-[#FC8505]/10 p-4 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
                        @if ($licensePlateLookup['success'] && $licensePlateLookup['vehicle'])
                            @php
                                $identifiedVehicle = $licensePlateLookup['vehicle'];
                                $vehicleTitle = trim(($identifiedVehicle['brand'] ?? '') . ' ' . ($identifiedVehicle['model'] ?? ''));
                                $vehicleDetails = array_filter([
                                    $identifiedVehicle['year'] ?? null,
                                    $identifiedVehicle['engine'] ?? null,
                                    $identifiedVehicle['fuel'] ?? null,
                                ]);
                            @endphp

                            <p class="font-black text-zinc-950 dark:text-zinc-50">Véhicule identifié</p>

                            @if ($vehicleTitle !== '')
                                <p class="mt-1 text-base font-black text-[#C96504] dark:text-orange-200">{{ $vehicleTitle }}</p>
                            @endif

                            @if (! empty($vehicleDetails))
                                <p class="mt-1 font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ implode(' · ', $vehicleDetails) }}
                                </p>
                            @endif

                            <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                                Résultats correspondant à votre véhicule avec les informations disponibles.
                            </p>
                        @else
                            <p class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $licensePlateLookup['message'] }}
                            </p>
                            <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                                Vous pouvez modifier ou supprimer la plaque, ou utiliser les champs de recherche manuelle.
                            </p>
                        @endif

                        <p class="mt-2 font-black text-[#C96504] dark:text-orange-200">
                            Plaque saisie : {{ $licensePlateLookup['normalized_plate'] }}
                        </p>
                    </section>
                @endif

                <section class="mt-4 flex gap-2 overflow-x-auto pb-1">
                    <span class="shrink-0 rounded-full border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-3 py-2 text-xs font-black text-zinc-800 dark:text-zinc-200">
                        Filtres
                    </span>
                    <span class="shrink-0 rounded-full border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-3 py-2 text-xs font-black text-zinc-800 dark:text-zinc-200">
                        Trier
                    </span>
                    <span class="shrink-0 rounded-full bg-[#FC8505]/10 px-3 py-2 text-xs font-black text-[#C96504] dark:text-orange-200">
                        Disponible
                    </span>
                    <span class="shrink-0 rounded-full bg-white dark:bg-zinc-900 px-3 py-2 text-xs font-bold text-zinc-600 dark:text-zinc-400 ring-1 ring-zinc-200 dark:ring-zinc-700">
                        Prix
                    </span>
                    <span class="shrink-0 rounded-full bg-white dark:bg-zinc-900 px-3 py-2 text-xs font-bold text-zinc-600 dark:text-zinc-400 ring-1 ring-zinc-200 dark:ring-zinc-700">
                        Distance
                    </span>
                </section>

                @if ($parts->isEmpty())
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white dark:border-[#FC8505]/30 dark:bg-zinc-900 p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Aucune pièce ne correspond à votre recherche.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            Essayez avec une autre pièce, marque, catégorie ou ville.
                        </p>
                    </section>

                    <section class="mt-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Enregistrer ma recherche</h2>
                                <p class="mt-1 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                                    Pièce Radar pourra repérer une future arrivée correspondant à votre besoin.
                                </p>
                            </div>

                            @auth
                                @if ($isBuyer)
                                    <a href="{{ route('client.saved-searches.index') }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                        Mes recherches
                                    </a>
                                @endif
                            @endauth
                        </div>

                        @auth
                            @if ($isBuyer)
                                <form method="POST" action="{{ route('client.saved-searches.store') }}" class="mt-4 space-y-4">
                                    @csrf

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="part_name" class="text-sm font-black text-zinc-900 dark:text-zinc-100">Pièce recherchée</label>
                                            <input
                                                id="part_name"
                                                name="part_name"
                                                type="text"
                                                value="{{ old('part_name', request('q')) }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('part_name')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="part_category" class="text-sm font-black text-zinc-900 dark:text-zinc-100">Catégorie</label>
                                            <input
                                                id="part_category"
                                                name="part_category"
                                                type="text"
                                                value="{{ old('part_category', request('category')) }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('part_category')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="vehicle_brand" class="text-sm font-black text-zinc-900 dark:text-zinc-100">Marque du véhicule</label>
                                            <input
                                                id="vehicle_brand"
                                                name="vehicle_brand"
                                                type="text"
                                                value="{{ old('vehicle_brand', request('brand')) }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('vehicle_brand')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="vehicle_model" class="text-sm font-black text-zinc-900 dark:text-zinc-100">Modèle du véhicule</label>
                                            <input
                                                id="vehicle_model"
                                                name="vehicle_model"
                                                type="text"
                                                value="{{ old('vehicle_model', request('model')) }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('vehicle_model')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="vehicle_year" class="text-sm font-black text-zinc-900 dark:text-zinc-100">Année</label>
                                            <input
                                                id="vehicle_year"
                                                name="vehicle_year"
                                                type="number"
                                                min="1900"
                                                max="{{ now()->year + 1 }}"
                                                value="{{ old('vehicle_year') }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 text-sm font-medium text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('vehicle_year')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950 sm:w-auto"
                                    >
                                        Enregistrer ma recherche
                                    </button>
                                </form>
                            @else
                                <p class="mt-4 rounded-xl bg-zinc-50 dark:bg-zinc-800 p-3 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                                    Connectez-vous avec un compte client pour enregistrer une recherche.
                                </p>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950 sm:w-auto">
                                Se connecter pour enregistrer ma recherche
                            </a>
                        @endauth
                    </section>
                @else
                    <section class="mt-5 space-y-2.5">
                        @php
                            $conditionLabels = [
                                'unknown' => 'État non précisé',
                                'used_good' => 'Occasion bon état',
                                'used_average' => 'Occasion état moyen',
                                'damaged' => 'Endommagée',
                            ];
                        @endphp

                        @foreach ($parts as $part)
                            @php
                                $vehicle = $part->vehicle;
                                $scrapyard = $vehicle?->scrapyard;
                                $partImage = $part->images->first();
                            @endphp

                            <article class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-3 shadow-sm transition hover:border-orange-200 hover:shadow-md">
                                <div class="flex gap-3">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800 ring-1 ring-zinc-200 dark:ring-zinc-700 sm:h-24 sm:w-24">
                                        @if ($partImage)
                                            <img src="{{ $partImage->url }}" alt="Photo {{ $part->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="h-10 w-14 rounded-md border border-[#FC8505]/50 bg-white dark:bg-zinc-900 shadow-inner sm:h-12 sm:w-16"></div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0 flex-1">
                                                <h2 class="truncate text-base font-black leading-5 text-zinc-950 dark:text-zinc-50">
                                                    {{ $part->name }}
                                                </h2>

                                                <p class="mt-1 truncate text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                                    {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                                    @if ($vehicle?->year)
                                                        · {{ $vehicle->year }}
                                                    @endif
                                                </p>

                                                <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $scrapyard?->name ?? 'Casse non renseignée' }}
                                                    @if ($scrapyard?->city)
                                                        · {{ $scrapyard->city }}
                                                    @endif
                                                </p>

                                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                                                </p>
                                            </div>

                                            <div class="shrink-0 text-left sm:text-right">
                                                <p class="text-base font-black text-[#FC8505] sm:text-lg">
                                                    @if ($part->price !== null)
                                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                                    @else
                                                        Prix sur demande
                                                    @endif
                                                </p>

                                                <span class="mt-1 inline-flex rounded-full bg-[#FC8505]/10 px-2.5 py-1 text-[11px] font-black text-[#C96504] dark:text-orange-200">
                                                    Disponible
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800 pt-2">
                                            <span class="text-xs font-medium text-zinc-400">Pièce publiée</span>
                                            <a href="{{ route('pieces.show', $part) }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                                Voir détail
                                            </a>
                                        </div>
                                    </div>
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
