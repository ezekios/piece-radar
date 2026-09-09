<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Résultats de recherche - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-20 pt-5 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <x-brand-logo :href="route('home')" image-class="h-9 w-auto max-w-[145px] object-contain" />
                        <div class="flex flex-wrap items-center gap-2">
                            @auth
                                @if (auth()->user()->role === 'client')
                                    <a href="{{ route('client.account.show') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                        Mon compte
                                    </a>

                                    <a href="{{ route('client.saved-searches.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                        Mes recherches
                                    </a>

                                    <a href="{{ route('client.requests.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                        Mes demandes
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
                                @endif
                            @endauth

                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                                Martinique
                            </span>
                        </div>
                    </div>

                    <h1 class="text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Résultats de recherche</h1>
                    <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                        {{ $parts->count() }} pièces disponibles autour de vous
                    </p>
                </header>

                @if (session('success'))
                    <div class="mt-4 rounded-2xl border border-orange-200 bg-white p-4 text-sm font-bold text-[#C96504] shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('client.parts.index') }}" class="mt-4 rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="q" class="text-xs font-black text-zinc-700">Rechercher une pièce</label>
                            <input
                                id="q"
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="Ex. phare, alternateur, référence"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="license_plate" class="text-xs font-black text-zinc-700">Plaque d’immatriculation</label>
                            <input
                                id="license_plate"
                                name="license_plate"
                                type="text"
                                value="{{ request('license_plate') }}"
                                placeholder="Ex. AB-123-CD"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium uppercase text-zinc-700 placeholder:normal-case placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div>
                            <label for="category" class="text-xs font-black text-zinc-700">Catégorie</label>
                            <input
                                id="category"
                                name="category"
                                type="text"
                                value="{{ request('category') }}"
                                placeholder="Optique, moteur..."
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div>
                            <label for="brand" class="text-xs font-black text-zinc-700">Marque</label>
                            <input
                                id="brand"
                                name="brand"
                                type="text"
                                value="{{ request('brand') }}"
                                placeholder="Renault"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div>
                            <label for="model" class="text-xs font-black text-zinc-700">Modèle</label>
                            <input
                                id="model"
                                name="model"
                                type="text"
                                value="{{ request('model') }}"
                                placeholder="Clio IV"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div>
                            <label for="city" class="text-xs font-black text-zinc-700">Ville</label>
                            <input
                                id="city"
                                name="city"
                                type="text"
                                value="{{ request('city') }}"
                                placeholder="Fort-de-France"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>
                    </div>

                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <button
                            type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-xl bg-[#FC8505] px-5 text-sm font-black text-white transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                        >
                            Rechercher
                        </button>

                        <a href="{{ route('client.parts.index') }}" class="inline-flex h-11 items-center justify-center text-sm font-bold text-zinc-500 hover:text-zinc-800">
                            Réinitialiser
                        </a>
                    </div>
                </form>

                @if ($hasLicensePlate)
                    <section class="mt-3 rounded-2xl border border-orange-100 bg-[#FC8505]/5 p-4 text-sm leading-6 text-zinc-700">
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

                            <p class="font-black text-zinc-950">Véhicule identifié</p>

                            @if ($vehicleTitle !== '')
                                <p class="mt-1 text-base font-black text-[#C96504]">{{ $vehicleTitle }}</p>
                            @endif

                            @if (! empty($vehicleDetails))
                                <p class="mt-1 font-medium text-zinc-700">
                                    {{ implode(' · ', $vehicleDetails) }}
                                </p>
                            @endif

                            <p class="mt-2 text-zinc-600">
                                Résultats correspondant à votre véhicule avec les informations disponibles.
                            </p>
                        @else
                            <p class="font-bold text-zinc-900">
                                {{ $licensePlateLookup['message'] }}
                            </p>
                            <p class="mt-1 text-zinc-600">
                                Vous pouvez modifier ou supprimer la plaque, ou utiliser les champs de recherche manuelle.
                            </p>
                        @endif

                        <p class="mt-2 font-black text-[#C96504]">
                            Plaque saisie : {{ $licensePlateLookup['normalized_plate'] }}
                        </p>
                    </section>
                @endif

                <section class="mt-4 flex gap-2 overflow-x-auto pb-1">
                    <span class="shrink-0 rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-black text-zinc-800">
                        Filtres
                    </span>
                    <span class="shrink-0 rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-black text-zinc-800">
                        Trier
                    </span>
                    <span class="shrink-0 rounded-full bg-[#FC8505]/10 px-3 py-2 text-xs font-black text-[#C96504]">
                        Disponible
                    </span>
                    <span class="shrink-0 rounded-full bg-white px-3 py-2 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                        Prix
                    </span>
                    <span class="shrink-0 rounded-full bg-white px-3 py-2 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                        Distance
                    </span>
                </section>

                @if ($parts->isEmpty())
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune pièce ne correspond à votre recherche.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Essayez avec une autre pièce, marque, catégorie ou ville.
                        </p>
                    </section>

                    <section class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-black text-zinc-950">Enregistrer ma recherche</h2>
                                <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                                    Pièce Radar pourra repérer une future arrivée correspondant à votre besoin.
                                </p>
                            </div>

                            @auth
                                @if (auth()->user()->role === 'client')
                                    <a href="{{ route('client.saved-searches.index') }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                        Mes recherches
                                    </a>
                                @endif
                            @endauth
                        </div>

                        @auth
                            @if (auth()->user()->role === 'client')
                                <form method="POST" action="{{ route('client.saved-searches.store') }}" class="mt-4 space-y-4">
                                    @csrf

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="part_name" class="text-sm font-black text-zinc-900">Pièce recherchée</label>
                                            <input
                                                id="part_name"
                                                name="part_name"
                                                type="text"
                                                value="{{ old('part_name', request('q')) }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('part_name')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="part_category" class="text-sm font-black text-zinc-900">Catégorie</label>
                                            <input
                                                id="part_category"
                                                name="part_category"
                                                type="text"
                                                value="{{ old('part_category', request('category')) }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('part_category')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="vehicle_brand" class="text-sm font-black text-zinc-900">Marque du véhicule</label>
                                            <input
                                                id="vehicle_brand"
                                                name="vehicle_brand"
                                                type="text"
                                                value="{{ old('vehicle_brand', request('brand')) }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('vehicle_brand')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="vehicle_model" class="text-sm font-black text-zinc-900">Modèle du véhicule</label>
                                            <input
                                                id="vehicle_model"
                                                name="vehicle_model"
                                                type="text"
                                                value="{{ old('vehicle_model', request('model')) }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('vehicle_model')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="vehicle_year" class="text-sm font-black text-zinc-900">Année</label>
                                            <input
                                                id="vehicle_year"
                                                name="vehicle_year"
                                                type="number"
                                                min="1900"
                                                max="{{ now()->year + 1 }}"
                                                value="{{ old('vehicle_year') }}"
                                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                            >
                                            @error('vehicle_year')
                                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto"
                                    >
                                        Enregistrer ma recherche
                                    </button>
                                </form>
                            @else
                                <p class="mt-4 rounded-xl bg-zinc-50 p-3 text-sm font-medium leading-6 text-zinc-600">
                                    Connectez-vous avec un compte client pour enregistrer une recherche.
                                </p>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
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

                            <article class="rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:border-orange-200 hover:shadow-md">
                                <div class="flex gap-3">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200 sm:h-24 sm:w-24">
                                        @if ($partImage)
                                            <img src="{{ $partImage->url }}" alt="Photo {{ $part->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="h-10 w-14 rounded-md border border-[#FC8505]/50 bg-white shadow-inner sm:h-12 sm:w-16"></div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex gap-3">
                                            <div class="min-w-0 flex-1">
                                                <h2 class="truncate text-base font-black leading-5 text-zinc-950">
                                                    {{ $part->name }}
                                                </h2>

                                                <p class="mt-1 truncate text-xs font-semibold text-zinc-700">
                                                    {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                                    @if ($vehicle?->year)
                                                        · {{ $vehicle->year }}
                                                    @endif
                                                </p>

                                                <p class="mt-1 truncate text-xs text-zinc-500">
                                                    {{ $scrapyard?->name ?? 'Casse non renseignée' }}
                                                    @if ($scrapyard?->city)
                                                        · {{ $scrapyard->city }}
                                                    @endif
                                                </p>

                                                <p class="mt-1 text-xs text-zinc-500">
                                                    {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                                                </p>
                                            </div>

                                            <div class="shrink-0 text-right">
                                                <p class="text-base font-black text-[#FC8505] sm:text-lg">
                                                    @if ($part->price !== null)
                                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                                    @else
                                                        Prix sur demande
                                                    @endif
                                                </p>

                                                <span class="mt-1 inline-flex rounded-full bg-[#FC8505]/10 px-2.5 py-1 text-[11px] font-black text-[#C96504]">
                                                    Disponible
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-2">
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

        <nav class="fixed inset-x-0 bottom-0 border-t border-zinc-200 bg-white/95 px-4 py-2 backdrop-blur sm:hidden">
            <div class="mx-auto grid max-w-md grid-cols-4 gap-2 text-center text-[11px] font-bold">
                <a href="{{ route('home') }}" class="text-zinc-500">
                    Accueil
                </a>
                <a href="{{ route('client.parts.index') }}" class="text-[#FC8505]" aria-current="page">
                    Recherche
                </a>
                <a href="{{ route('client.requests.index') }}" class="text-zinc-500">
                    Demandes
                </a>
                @auth
                    @if (auth()->user()->role === 'scrapyard')
                        <a href="{{ route('scrapyard.dashboard') }}" class="text-zinc-500">
                            Compte
                        </a>
                    @else
                        <a href="{{ route('client.account.show') }}" class="text-zinc-500">
                            Compte
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="text-zinc-500">
                        Compte
                    </a>
                @endauth
            </div>
        </nav>
    </body>
</html>
