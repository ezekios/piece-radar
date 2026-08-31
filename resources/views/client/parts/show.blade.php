<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $part->name }} - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $vehicle = $part->vehicle;
            $scrapyard = $vehicle?->scrapyard;
            $mainImage = $part->images->first();
            $conditionLabels = [
                'unknown' => 'État non précisé',
                'used_good' => 'Occasion bon état',
                'used_average' => 'Occasion état moyen',
                'damaged' => 'Endommagée',
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-10 pt-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                @if (session('success'))
                    <div class="mb-4 rounded-2xl border border-orange-200 bg-white p-4 text-sm font-bold text-[#C96504] shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <header class="mb-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <a href="{{ route('client.parts.index') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                            Retour vers les résultats
                        </a>

                        @auth
                            @if (auth()->user()->role === 'client')
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('client.requests.index') }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                        Mes demandes
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="text-sm font-bold text-zinc-500 hover:text-zinc-900">
                                            Déconnexion
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>

                    <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="flex gap-4">
                            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200 sm:h-32 sm:w-32">
                                @if ($mainImage)
                                    <img src="{{ $mainImage->url }}" alt="Photo {{ $part->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-12 w-16 rounded-md border border-[#FC8505]/50 bg-white shadow-inner sm:h-16 sm:w-20"></div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                                        <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950">
                                            {{ $part->name }}
                                        </h1>
                                    </div>

                                    <span class="rounded-full bg-[#FC8505]/10 px-3 py-1 text-xs font-black text-[#C96504]">
                                        Disponible
                                    </span>
                                </div>

                                <p class="mt-4 text-3xl font-black text-[#FC8505]">
                                    @if ($part->price !== null)
                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                    @else
                                        Prix sur demande
                                    @endif
                                </p>

                                <p class="mt-2 text-sm font-medium text-zinc-600">
                                    {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="space-y-3">
                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Photos</h2>

                        @if ($part->images->isEmpty())
                            <div class="mt-3 flex aspect-[4/3] w-full items-center justify-center rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                                <div class="h-16 w-24 rounded-lg border border-[#FC8505]/50 bg-white shadow-inner"></div>
                            </div>
                        @else
                            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($part->images as $image)
                                    <img src="{{ $image->url }}" alt="Photo pièce {{ $loop->iteration }}" class="aspect-[4/3] w-full rounded-xl object-cover ring-1 ring-zinc-200">
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Informations pièce</h2>

                        <dl class="mt-3 space-y-3 text-sm">
                            @if ($part->reference)
                                <div class="flex justify-between gap-4 border-b border-zinc-100 pb-3">
                                    <dt class="font-medium text-zinc-500">Référence</dt>
                                    <dd class="text-right font-bold text-zinc-900">{{ $part->reference }}</dd>
                                </div>
                            @endif

                            @if ($part->oem_reference)
                                <div class="flex justify-between gap-4 border-b border-zinc-100 pb-3">
                                    <dt class="font-medium text-zinc-500">Référence OEM</dt>
                                    <dd class="text-right font-bold text-zinc-900">{{ $part->oem_reference }}</dd>
                                </div>
                            @endif

                            <div class="flex justify-between gap-4">
                                <dt class="font-medium text-zinc-500">État</dt>
                                <dd class="text-right font-bold text-zinc-900">
                                    {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                                </dd>
                            </div>
                        </dl>

                        @if ($part->description)
                            <div class="mt-4 rounded-xl bg-zinc-50 p-3">
                                <p class="text-sm leading-6 text-zinc-600">{{ $part->description }}</p>
                            </div>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Véhicule associé</h2>

                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="font-medium text-zinc-500">Marque</dt>
                                <dd class="mt-1 font-bold text-zinc-900">{{ $vehicle?->brand ?? 'Non renseignée' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Modèle</dt>
                                <dd class="mt-1 font-bold text-zinc-900">{{ $vehicle?->model ?? 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Année</dt>
                                <dd class="mt-1 font-bold text-zinc-900">{{ $vehicle?->year ?? 'Non renseignée' }}</dd>
                            </div>

                            @if ($vehicle?->engine)
                                <div>
                                    <dt class="font-medium text-zinc-500">Motorisation</dt>
                                    <dd class="mt-1 font-bold text-zinc-900">{{ $vehicle->engine }}</dd>
                                </div>
                            @endif

                            @if ($vehicle?->fuel)
                                <div>
                                    <dt class="font-medium text-zinc-500">Carburant</dt>
                                    <dd class="mt-1 font-bold text-zinc-900">{{ $vehicle->fuel }}</dd>
                                </div>
                            @endif

                            @if ($vehicle?->mileage)
                                <div>
                                    <dt class="font-medium text-zinc-500">Kilométrage</dt>
                                    <dd class="mt-1 font-bold text-zinc-900">{{ number_format($vehicle->mileage, 0, ',', ' ') }} km</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Casse automobile</h2>

                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex justify-between gap-4 border-b border-zinc-100 pb-3">
                                <dt class="font-medium text-zinc-500">Nom</dt>
                                <dd class="text-right font-bold text-zinc-900">{{ $scrapyard?->name ?? 'Non renseigné' }}</dd>
                            </div>

                            <div class="flex justify-between gap-4 border-b border-zinc-100 pb-3">
                                <dt class="font-medium text-zinc-500">Ville</dt>
                                <dd class="text-right font-bold text-zinc-900">{{ $scrapyard?->city ?? 'Non renseignée' }}</dd>
                            </div>

                            @if ($scrapyard?->phone)
                                <div class="flex justify-between gap-4">
                                    <dt class="font-medium text-zinc-500">Téléphone</dt>
                                    <dd class="text-right font-bold text-zinc-900">{{ $scrapyard->phone }}</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    <a
                        href="{{ route('pieces.request', $part) }}"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                    >
                        Demander une mise de côté
                    </a>
                </div>
            </div>
        </main>
    </body>
</html>
