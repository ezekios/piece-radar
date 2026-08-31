<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Véhicules de la casse - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-4xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                            @include('scrapyard.partials.navigation')
                            <h1 class="mt-4 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Véhicules de la casse</h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $scrapyard?->name ?? 'Aucune casse disponible' }}
                                @if ($scrapyard?->city)
                                    · {{ $scrapyard->city }}
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                                {{ $vehicles->count() }} véhicule{{ $vehicles->count() > 1 ? 's' : '' }} affiché{{ $vehicles->count() > 1 ? 's' : '' }}
                            </span>

                            @if ($scrapyard)
                                <a href="{{ route('scrapyard.vehicles.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                    Ajouter un véhicule
                                </a>
                            @endif
                        </div>
                    </div>
                </header>

                @if (! $scrapyard)
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune casse n’est disponible.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            La liste des véhicules s’affichera dès qu’une casse existera en base.
                        </p>
                    </section>
                @else
                    <form method="GET" action="{{ route('scrapyard.vehicles.index') }}" class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <label for="q" class="text-xs font-black text-zinc-700">Rechercher un véhicule</label>
                        <input
                            id="q"
                            name="q"
                            type="search"
                            value="{{ request('q') }}"
                            placeholder="Marque, modèle, année, immatriculation..."
                            class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                        >

                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <button
                                type="submit"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-[#FC8505] px-5 text-sm font-black text-white transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                            >
                                Rechercher
                            </button>

                            <a href="{{ route('scrapyard.vehicles.index') }}" class="inline-flex h-11 items-center justify-center text-sm font-bold text-zinc-500 hover:text-zinc-800">
                                Réinitialiser
                            </a>
                        </div>
                    </form>

                    @if ($vehicles->isEmpty())
                        <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                            <h2 class="text-base font-black text-zinc-950">Aucun véhicule trouvé pour le moment.</h2>
                        </section>
                    @else
                        <section class="mt-5 space-y-3">
                            @foreach ($vehicles as $vehicle)
                                @php
                                    $vehicleImage = $vehicle->images->first();
                                @endphp

                                <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="flex min-w-0 gap-3">
                                            <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                                                @if ($vehicleImage)
                                                    <img src="{{ $vehicleImage->url }}" alt="Photo {{ $vehicle->brand }} {{ $vehicle->model }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="h-10 w-14 rounded-md border border-[#FC8505]/50 bg-white shadow-inner"></div>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <h2 class="truncate text-base font-black text-zinc-950">
                                                    {{ $vehicle->brand }} {{ $vehicle->model }}
                                                </h2>
                                                <p class="mt-1 text-xs font-semibold text-zinc-700">
                                                    @if ($vehicle->year)
                                                        {{ $vehicle->year }}
                                                    @else
                                                        Année non renseignée
                                                    @endif
                                                </p>
                                                <p class="mt-1 text-xs font-medium text-zinc-500">
                                                    {{ $vehicle->license_plate ?: 'Immatriculation non renseignée' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="shrink-0 rounded-2xl bg-[#FC8505]/10 px-4 py-3 text-center sm:self-start">
                                            <p class="text-2xl font-black text-[#FC8505]">{{ $vehicle->parts_count }}</p>
                                            <p class="text-xs font-black text-[#C96504]">Pièces</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm sm:grid-cols-3">
                                        <div>
                                            <p class="text-xs font-bold text-zinc-500">Carburant</p>
                                            <p class="mt-1 font-black text-zinc-950">{{ $vehicle->fuel ?: 'Non renseigné' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold text-zinc-500">Motorisation</p>
                                            <p class="mt-1 font-black text-zinc-950">{{ $vehicle->engine ?: 'Non renseignée' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold text-zinc-500">Kilométrage</p>
                                            <p class="mt-1 font-black text-zinc-950">
                                                @if ($vehicle->mileage)
                                                    {{ number_format($vehicle->mileage, 0, ',', ' ') }} km
                                                @else
                                                    Non renseigné
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3 border-t border-zinc-100 pt-3">
                                        <p class="text-xs font-medium text-zinc-400">
                                            Ajouté le {{ $vehicle->created_at?->format('d/m/Y à H:i') }}
                                        </p>

                                        <div class="mt-3">
                                            <p class="text-xs font-black uppercase text-zinc-500">Actions rapides</p>

                                            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                                                <a href="{{ route('scrapyard.vehicles.show', $vehicle) }}" class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-[#FC8505]/30 bg-white px-4 text-sm font-black text-[#FC8505] transition hover:bg-[#FC8505]/10 focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                                    Voir le véhicule
                                                </a>

                                                <a href="{{ route('scrapyard.vehicles.parts.create', $vehicle) }}" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-[#FC8505] px-4 text-sm font-black text-white transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                                    Ajouter une pièce
                                                </a>

                                                @if ($vehicle->parts_count > 0)
                                                    <a href="{{ route('scrapyard.vehicles.show', $vehicle) }}" class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                                        Voir les pièces associées
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </section>
                    @endif
                @endif
            </div>
        </main>
    </body>
</html>
