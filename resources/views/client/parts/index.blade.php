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
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                            Martinique
                        </span>
                    </div>

                    <h1 class="text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Résultats de recherche</h1>
                    <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                        {{ $parts->count() }} pièces disponibles autour de vous
                    </p>
                </header>

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

                <section class="mt-4 flex gap-2 overflow-x-auto pb-1">
                    <button type="button" class="shrink-0 rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-black text-zinc-800">
                        Filtres
                    </button>
                    <button type="button" class="shrink-0 rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-black text-zinc-800">
                        Trier
                    </button>
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
                            @endphp

                            <article class="rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:border-orange-200 hover:shadow-md">
                                <div class="flex gap-3">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-zinc-100 ring-1 ring-zinc-200 sm:h-24 sm:w-24">
                                        <div class="h-10 w-14 rounded-md border border-[#FC8505]/50 bg-white shadow-inner sm:h-12 sm:w-16"></div>
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
                <button type="button" class="text-zinc-500">
                    Accueil
                </button>
                <button type="button" class="text-[#FC8505]">
                    Recherche
                </button>
                <button type="button" class="text-zinc-500">
                    Demandes
                </button>
                <button type="button" class="text-zinc-500">
                    Compte
                </button>
            </div>
        </nav>
    </body>
</html>
