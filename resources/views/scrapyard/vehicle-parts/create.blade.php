<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ajouter une pièce - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $conditionLabels = [
                'unknown' => 'État non précisé',
                'used_good' => 'Occasion bon état',
                'used_average' => 'Occasion état moyen',
                'damaged' => 'Endommagée',
            ];

            $statusLabels = [
                'preparing' => 'En préparation',
                'available' => 'Disponible',
                'reserved' => 'Mise de côté',
                'sold' => 'Vendue',
                'unavailable' => 'Non disponible',
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <a href="{{ route('scrapyard.vehicles.show', $vehicle) }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour vers le véhicule
                    </a>

                    <div class="mt-4">
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Ajouter une pièce</h1>
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
                    <section class="mt-5 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-[#FC8505]">Véhicule concerné</p>
                        <h2 class="mt-1 text-lg font-black text-zinc-950">
                            {{ $vehicle->brand }} {{ $vehicle->model }}
                        </h2>
                        <p class="mt-1 text-sm font-medium text-zinc-600">
                            @if ($vehicle->year)
                                {{ $vehicle->year }}
                            @else
                                Année non renseignée
                            @endif
                        </p>
                    </section>

                    <form method="POST" action="{{ route('scrapyard.vehicles.parts.store', $vehicle) }}" class="mt-4 space-y-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="text-sm font-black text-zinc-900">Nom de la pièce</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                placeholder="Phare avant droit"
                            >
                            @error('name')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="text-sm font-black text-zinc-900">Catégorie</label>
                            <input
                                id="category"
                                name="category"
                                type="text"
                                value="{{ old('category') }}"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                placeholder="Optique"
                            >
                            @error('category')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="text-sm font-black text-zinc-900">Prix</label>
                            <input
                                id="price"
                                name="price"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('price') }}"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                placeholder="85"
                            >
                            @error('price')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="condition" class="text-sm font-black text-zinc-900">État</label>
                            <select
                                id="condition"
                                name="condition"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                                @foreach ($conditionLabels as $condition => $label)
                                    <option value="{{ $condition }}" @selected(old('condition', 'unknown') === $condition)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('condition')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="text-sm font-black text-zinc-900">Statut</label>
                            <select
                                id="status"
                                name="status"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                                @foreach ($statusLabels as $status => $label)
                                    <option value="{{ $status }}" @selected(old('status', 'preparing') === $status)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reference" class="text-sm font-black text-zinc-900">Référence</label>
                            <input
                                id="reference"
                                name="reference"
                                type="text"
                                value="{{ old('reference') }}"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                placeholder="REF-123"
                            >
                            @error('reference')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="oem_reference" class="text-sm font-black text-zinc-900">Référence OEM</label>
                            <input
                                id="oem_reference"
                                name="oem_reference"
                                type="text"
                                value="{{ old('oem_reference') }}"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                placeholder="OEM-456"
                            >
                            @error('oem_reference')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="text-sm font-black text-zinc-900">Description</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                class="mt-2 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                placeholder="Informations utiles sur l’état ou la compatibilité."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                        >
                            Ajouter la pièce
                        </button>
                    </form>
                @endif
            </div>
        </main>
    </body>
</html>
