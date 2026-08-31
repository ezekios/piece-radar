<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Demande de mise de côté - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $vehicle = $part->vehicle;
            $scrapyard = $vehicle?->scrapyard;
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-10 pt-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="mb-4">
                    <a href="{{ route('pieces.show', $part) }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour à la pièce
                    </a>

                    <div class="mt-4">
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                            Demande de mise de côté
                        </h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            Votre compte client sera associé à cette demande.
                        </p>
                    </div>
                </header>

                <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div class="flex gap-3">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                            <div class="h-10 w-14 rounded-md border border-[#FC8505]/50 bg-white shadow-inner"></div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="truncate text-base font-black text-zinc-950">{{ $part->name }}</h2>
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
                                </div>

                                <p class="shrink-0 text-base font-black text-[#FC8505]">
                                    @if ($part->price !== null)
                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                    @else
                                        Prix sur demande
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-zinc-950">Compte client</h2>
                            <p class="mt-1 text-sm font-medium text-zinc-600">
                                Ces coordonnées proviennent de votre compte connecté.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                Déconnexion
                            </button>
                        </form>
                    </div>

                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div class="rounded-xl bg-zinc-50 p-3">
                            <dt class="text-xs font-bold text-zinc-500">Nom</dt>
                            <dd class="mt-1 font-black text-zinc-950">{{ $client?->name }}</dd>
                        </div>

                        <div class="rounded-xl bg-zinc-50 p-3">
                            <dt class="text-xs font-bold text-zinc-500">Téléphone</dt>
                            <dd class="mt-1 font-black text-zinc-950">{{ $client?->phone ?? 'Non renseigné' }}</dd>
                        </div>

                        <div class="rounded-xl bg-zinc-50 p-3">
                            <dt class="text-xs font-bold text-zinc-500">Email</dt>
                            <dd class="mt-1 break-words font-black text-zinc-950">{{ $client?->email }}</dd>
                        </div>
                    </dl>
                </section>

                <form method="POST" action="{{ route('pieces.request.store', $part) }}" class="mt-4 space-y-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    @csrf

                    <div>
                        <label for="customer_message" class="text-sm font-black text-zinc-900">Message à la casse</label>
                        <textarea
                            id="customer_message"
                            name="customer_message"
                            rows="5"
                            class="mt-2 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            placeholder="Bonjour, je souhaite mettre cette pièce de côté."
                        >{{ old('customer_message') }}</textarea>
                        @error('customer_message')
                            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                    >
                        Envoyer la demande
                    </button>
                </form>
            </div>
        </main>
    </body>
</html>
