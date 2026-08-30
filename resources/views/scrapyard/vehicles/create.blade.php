<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ajouter un véhicule - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div>
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        @include('scrapyard.partials.navigation')
                        <a href="{{ route('scrapyard.vehicles.index') }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                            Retour vers les véhicules
                        </a>
                        <h1 class="mt-4 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Ajouter un véhicule</h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            {{ $scrapyard?->name ?? 'Aucune casse disponible' }}
                            @if ($scrapyard?->city)
                                · {{ $scrapyard->city }}
                            @endif
                        </p>
                    </div>
                </header>

                @if (! $scrapyard)
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune casse n’est disponible.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Le formulaire sera disponible dès qu’une casse existera en base.
                        </p>
                    </section>
                @else
                    <form method="POST" action="{{ route('scrapyard.vehicles.store') }}" class="mt-5 space-y-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="brand" class="text-sm font-black text-zinc-900">Marque</label>
                                <input
                                    id="brand"
                                    name="brand"
                                    type="text"
                                    value="{{ old('brand') }}"
                                    required
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                    placeholder="Renault"
                                >
                                @error('brand')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="model" class="text-sm font-black text-zinc-900">Modèle</label>
                                <input
                                    id="model"
                                    name="model"
                                    type="text"
                                    value="{{ old('model') }}"
                                    required
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                    placeholder="Clio IV"
                                >
                                @error('model')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="year" class="text-sm font-black text-zinc-900">Année</label>
                                <input
                                    id="year"
                                    name="year"
                                    type="number"
                                    min="1900"
                                    max="{{ (int) date('Y') + 1 }}"
                                    value="{{ old('year') }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                    placeholder="2017"
                                >
                                @error('year')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="license_plate" class="text-sm font-black text-zinc-900">Plaque d’immatriculation</label>
                                <input
                                    id="license_plate"
                                    name="license_plate"
                                    type="text"
                                    value="{{ old('license_plate') }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium uppercase text-zinc-900 placeholder:normal-case placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                    placeholder="AB-123-CD"
                                >
                                @error('license_plate')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="fuel" class="text-sm font-black text-zinc-900">Carburant</label>
                                <input
                                    id="fuel"
                                    name="fuel"
                                    type="text"
                                    value="{{ old('fuel') }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                    placeholder="Diesel"
                                >
                                @error('fuel')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="engine" class="text-sm font-black text-zinc-900">Motorisation</label>
                                <input
                                    id="engine"
                                    name="engine"
                                    type="text"
                                    value="{{ old('engine') }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                    placeholder="1.5 dCi"
                                >
                                @error('engine')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="mileage" class="text-sm font-black text-zinc-900">Kilométrage</label>
                                <input
                                    id="mileage"
                                    name="mileage"
                                    type="number"
                                    min="0"
                                    value="{{ old('mileage') }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                    placeholder="125000"
                                >
                                @error('mileage')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                        >
                            Ajouter le véhicule
                        </button>
                    </form>
                @endif
            </div>
        </main>
    </body>
</html>
