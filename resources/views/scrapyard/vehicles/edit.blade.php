<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Modifier le véhicule - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div>
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        @include('scrapyard.partials.navigation')
                        <a href="{{ route('scrapyard.vehicles.show', $vehicle) }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                            Retour vers le véhicule
                        </a>
                        <h1 class="mt-4 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Modifier le véhicule</h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            {{ $scrapyard->name }}
                            @if ($scrapyard->city)
                                · {{ $scrapyard->city }}
                            @endif
                        </p>
                    </div>
                </header>

                <form method="POST" action="{{ route('scrapyard.vehicles.update', $vehicle) }}" enctype="multipart/form-data" class="mt-5 space-y-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="brand" class="text-sm font-black text-zinc-900">Marque</label>
                            <input
                                id="brand"
                                name="brand"
                                type="text"
                                value="{{ old('brand', $vehicle->brand) }}"
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
                                value="{{ old('model', $vehicle->model) }}"
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
                                value="{{ old('year', $vehicle->year) }}"
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
                                value="{{ old('license_plate', $vehicle->license_plate) }}"
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
                                value="{{ old('fuel', $vehicle->fuel) }}"
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
                                value="{{ old('engine', $vehicle->engine) }}"
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
                                value="{{ old('mileage', $vehicle->mileage) }}"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                placeholder="125000"
                            >
                            @error('mileage')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <label for="photos" class="text-sm font-black text-zinc-900">Photos du véhicule</label>
                                <p class="mt-1 text-xs font-medium leading-5 text-zinc-500">
                                    Maximum 5 photos au total — JPG, PNG ou WebP — 5 Mo maximum par photo.
                                </p>
                            </div>

                            <span class="rounded-full bg-zinc-50 px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                                {{ $vehicle->images->count() }}/5
                            </span>
                        </div>

                        @if ($vehicle->images->isNotEmpty())
                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($vehicle->images as $image)
                                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-2">
                                        <img src="{{ $image->url }}" alt="Photo véhicule {{ $loop->iteration }}" class="aspect-[4/3] w-full rounded-lg object-cover">
                                        <button type="submit" form="delete-vehicle-image-{{ $image->id }}" class="mt-2 cursor-pointer text-xs font-black text-[#FC8505] hover:text-[#E87804]">
                                            Supprimer
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($vehicle->images->count() < 5)
                            <input
                                id="photos"
                                name="photos[]"
                                type="file"
                                multiple
                                accept="image/*"
                                class="mt-4 block w-full cursor-pointer text-sm font-medium text-zinc-700 file:mr-4 file:cursor-pointer file:rounded-xl file:border-0 file:bg-[#FC8505] file:px-4 file:py-2.5 file:text-sm file:font-black file:text-white hover:file:bg-[#E87804]"
                            >
                        @else
                            <p class="mt-4 rounded-xl bg-zinc-50 p-3 text-sm font-bold text-zinc-600">
                                La limite de 5 photos est atteinte.
                            </p>
                        @endif

                        @error('photos')
                            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                        @error('photos.*')
                            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                    >
                        Enregistrer les modifications
                    </button>
                </form>

                @foreach ($vehicle->images as $image)
                    <form id="delete-vehicle-image-{{ $image->id }}" method="POST" action="{{ route('scrapyard.vehicles.images.destroy', [$vehicle, $image]) }}">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </main>
    </body>
</html>
