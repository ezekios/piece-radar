<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Pièce Radar - Trouver une pièce auto en Martinique</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $user = auth()->user();
            $isClient = $user?->role === 'client';
            $isScrapyard = $user?->role === 'scrapyard';

            $conditionLabels = [
                'unknown' => 'État non précisé',
                'used_good' => 'Occasion bon état',
                'used_average' => 'Occasion état moyen',
                'damaged' => 'Endommagée',
            ];
        @endphp

        <main class="min-h-screen">
            <header class="border-b border-zinc-200/80 bg-white">
                <div class="mx-auto flex w-full max-w-6xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <a href="{{ route('home') }}" class="inline-flex flex-col">
                        <span class="text-base font-black text-[#FC8505]">Pièce Radar</span>
                        <span class="text-xs font-bold text-zinc-500">Pièces auto de casse, plus simples à trouver</span>
                    </a>

                    <nav class="flex flex-wrap items-center gap-2" aria-label="Navigation principale">
                        @if (! $isScrapyard)
                            <a href="{{ route('client.parts.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                Rechercher une pièce
                            </a>
                        @endif

                        <a href="#comment-ca-marche" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                            Comment ça marche
                        </a>

                        @guest
                            <a href="{{ route('login') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                Connexion
                            </a>

                            <a href="{{ route('client.register.create') }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-[#FC8505] px-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                Créer un compte
                            </a>
                        @else
                            @if ($isClient)
                                <a href="{{ route('client.requests.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-[#FC8505] px-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                    Mes demandes
                                </a>
                            @endif

                            @if ($isScrapyard)
                                <a href="{{ route('scrapyard.dashboard') }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-[#FC8505] px-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                    Espace casse
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button type="submit" class="inline-flex h-10 cursor-pointer items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                    Déconnexion
                                </button>
                            </form>
                        @endguest
                    </nav>
                </div>
            </header>

            <section class="mx-auto grid w-full max-w-6xl gap-6 px-4 py-8 sm:px-6 sm:py-10 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:px-8 lg:py-14">
                <div>
                    <span class="inline-flex rounded-full bg-[#FC8505]/10 px-3 py-1 text-xs font-black text-[#C96504] ring-1 ring-[#FC8505]/20">
                        Recherche de pièces auto en Martinique
                    </span>

                    <h1 class="mt-4 max-w-3xl text-3xl font-black leading-tight text-zinc-950 sm:text-5xl">
                        Trouvez la pièce auto qu'il vous faut dans les casses près de chez vous.
                    </h1>

                    <p class="mt-4 max-w-2xl text-base font-medium leading-7 text-zinc-600 sm:text-lg">
                        Pièce Radar centralise les pièces disponibles dans les casses automobiles pour vous aider à trouver rapidement la bonne pièce.
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('client.parts.index') }}" class="inline-flex h-12 items-center justify-center rounded-2xl bg-[#FC8505] px-5 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                            Rechercher une pièce
                        </a>
                        <a href="#comment-ca-marche" class="inline-flex h-12 items-center justify-center rounded-2xl border border-zinc-200 bg-white px-5 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                            Comment ça marche
                        </a>
                    </div>
                </div>

                <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-black text-[#FC8505]">Recherche rapide</p>
                    <h2 class="mt-1 text-xl font-black text-zinc-950">Décrivez la pièce ou le véhicule</h2>

                    <form method="GET" action="{{ route('client.parts.index') }}" class="mt-4 space-y-3">
                        <div>
                            <label for="q" class="text-xs font-black text-zinc-700">Pièce ou mot-clé</label>
                            <input
                                id="q"
                                name="q"
                                type="search"
                                placeholder="Alternateur, phare, rétroviseur..."
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="brand" class="text-xs font-black text-zinc-700">Marque</label>
                                <input
                                    id="brand"
                                    name="brand"
                                    type="text"
                                    placeholder="Peugeot"
                                    class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                >
                            </div>

                            <div>
                                <label for="model" class="text-xs font-black text-zinc-700">Modèle</label>
                                <input
                                    id="model"
                                    name="model"
                                    type="text"
                                    placeholder="208"
                                    class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="city" class="text-xs font-black text-zinc-700">Ville</label>
                            <input
                                id="city"
                                name="city"
                                type="text"
                                placeholder="Fort-de-France"
                                class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                        </div>

                        <button type="submit" class="inline-flex h-12 w-full cursor-pointer items-center justify-center rounded-2xl bg-[#FC8505] px-5 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                            Lancer la recherche
                        </button>
                    </form>
                </section>
            </section>

            <section id="comment-ca-marche" class="border-y border-zinc-200/80 bg-white">
                <div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-black text-zinc-950">Comment ça marche</h2>

                    <div class="mt-5 grid gap-3 md:grid-cols-3">
                        <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#FC8505]/10 text-sm font-black text-[#C96504]">1</span>
                            <h3 class="mt-4 text-base font-black text-zinc-950">Recherchez votre pièce</h3>
                            <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">Indiquez la pièce recherchée, la marque, le modèle ou la ville.</p>
                        </article>

                        <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#FC8505]/10 text-sm font-black text-[#C96504]">2</span>
                            <h3 class="mt-4 text-base font-black text-zinc-950">Consultez les pièces disponibles</h3>
                            <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">Comparez les pièces publiées par les casses, avec leurs photos et informations utiles.</p>
                        </article>

                        <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#FC8505]/10 text-sm font-black text-[#C96504]">3</span>
                            <h3 class="mt-4 text-base font-black text-zinc-950">Demandez une mise de côté</h3>
                            <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">Connectez-vous et envoyez votre demande. Lorsqu'une demande est acceptée, la pièce peut être réservée pendant 48 heures.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="mx-auto grid w-full max-w-6xl gap-4 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:px-8">
                <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-[#FC8505]">Pour les clients</p>
                    <h2 class="mt-2 text-2xl font-black text-zinc-950">Une recherche plus simple et un suivi clair</h2>
                    <ul class="mt-4 space-y-2 text-sm font-medium leading-6 text-zinc-600">
                        <li>Centralisation des pièces disponibles publiées par les casses.</li>
                        <li>Photos et détails pour mieux vérifier la pièce avant demande.</li>
                        <li>Suivi des demandes et historique dans votre espace client.</li>
                        <li>Réservation possible pendant 48 heures après acceptation.</li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-[#FC8505]">Pour les casses</p>
                    <h2 class="mt-2 text-2xl font-black text-zinc-950">Vous êtes une casse automobile ?</h2>
                    <p class="mt-3 text-sm font-medium leading-6 text-zinc-600">
                        Pièce Radar permet de gérer les véhicules, préparer les pièces, publier le stock, recevoir des demandes et suivre les réservations.
                    </p>

                    @guest
                        <a href="{{ route('login') }}" class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                            Accéder à l'espace casse
                        </a>
                    @else
                        @if ($isScrapyard)
                            <a href="{{ route('scrapyard.dashboard') }}" class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                Accéder à l'espace casse
                            </a>
                        @elseif ($isClient)
                            <p class="mt-5 rounded-xl bg-zinc-50 p-3 text-sm font-bold text-zinc-600">
                                Votre compte actuel est un compte client.
                            </p>
                        @endif
                    @endguest
                </article>
            </section>

            <section class="mx-auto w-full max-w-6xl px-4 pb-8 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase text-[#FC8505]">Stock publié</p>
                        <h2 class="mt-1 text-2xl font-black text-zinc-950">Pièces récemment disponibles</h2>
                    </div>

                    <a href="{{ route('client.parts.index') }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Voir toutes les pièces
                    </a>
                </div>

                @if ($recentParts->isEmpty())
                    <div class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h3 class="text-base font-black text-zinc-950">Aucune pièce disponible publiée pour le moment.</h3>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">Les pièces apparaîtront ici dès qu'une casse les publiera.</p>
                    </div>
                @else
                    <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($recentParts as $part)
                            @php
                                $vehicle = $part->vehicle;
                                $scrapyard = $vehicle?->scrapyard;
                                $partImage = $part->images->first();
                            @endphp

                            <article class="rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm">
                                <div class="flex aspect-[4/3] w-full items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                                    @if ($partImage)
                                        <img src="{{ $partImage->url }}" alt="Photo {{ $part->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-12 w-16 rounded-md border border-[#FC8505]/50 bg-white shadow-inner"></div>
                                    @endif
                                </div>

                                <h3 class="mt-3 truncate text-base font-black text-zinc-950">{{ $part->name }}</h3>
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
                                <p class="mt-2 text-sm font-black text-[#FC8505]">
                                    @if ($part->price !== null)
                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                    @else
                                        Prix sur demande
                                    @endif
                                </p>
                                <p class="mt-1 text-xs font-medium text-zinc-500">
                                    {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                                </p>

                                <a href="{{ route('pieces.show', $part) }}" class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-xl border border-[#FC8505]/30 bg-white px-3 text-sm font-black text-[#FC8505] transition hover:bg-[#FC8505]/10 focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                    Voir la pièce
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <footer class="border-t border-zinc-200/80 bg-white">
                <div class="mx-auto flex w-full max-w-6xl flex-col gap-4 px-4 py-6 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                    <div>
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        <p class="mt-1 text-sm font-medium text-zinc-600">Centrale de pièces automobiles issues de casses.</p>
                    </div>

                    <div class="flex flex-wrap gap-3 text-sm font-black">
                        <a href="{{ route('client.parts.index') }}" class="text-zinc-600 hover:text-[#FC8505]">Rechercher une pièce</a>
                        @guest
                            <a href="{{ route('login') }}" class="text-zinc-600 hover:text-[#FC8505]">Connexion</a>
                        @endguest
                    </div>
                </div>
            </footer>
        </main>
    </body>
</html>
