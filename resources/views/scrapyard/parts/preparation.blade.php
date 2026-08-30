<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Préparer la pièce - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $vehicle = $part->vehicle;
            $requestScrapyard = $vehicle?->scrapyard;

            $statusLabels = [
                'preparing' => 'En préparation',
                'available' => 'Disponible',
                'reserved' => 'Mise de côté',
                'sold' => 'Vendue',
                'unavailable' => 'Non disponible',
            ];

            $conditionLabels = [
                'unknown' => 'État non précisé',
                'used_good' => 'Occasion bon état',
                'used_average' => 'Occasion état moyen',
                'damaged' => 'Endommagée',
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <a href="{{ route('scrapyard.parts.show', $part) }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour vers la pièce
                    </a>

                    <div class="mt-4">
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Préparer la pièce</h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            {{ $requestScrapyard?->name ?? $scrapyard?->name ?? 'Casse non renseignée' }}
                            @if ($requestScrapyard?->city ?? $scrapyard?->city)
                                · {{ $requestScrapyard?->city ?? $scrapyard->city }}
                            @endif
                        </p>
                    </div>
                </header>

                <div class="mt-4 space-y-3">
                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-[#FC8505]">Véhicule associé</p>
                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="font-medium text-zinc-500">Marque</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->brand ?? 'Non renseignée' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Modèle</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->model ?? 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Année</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->year ?? 'Non renseignée' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-[#FC8505]">Pièce à vérifier</p>
                        <div class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                            <div class="sm:col-span-1">
                                <p class="font-medium text-zinc-500">Nom</p>
                                <p class="mt-1 font-black text-zinc-900">{{ $part->name }}</p>
                            </div>

                            <div>
                                <p class="font-medium text-zinc-500">Statut actuel</p>
                                <p class="mt-1 font-black text-zinc-900">{{ $statusLabels[$part->status] ?? $part->status }}</p>
                            </div>

                            <div>
                                <p class="font-medium text-zinc-500">Publication actuelle</p>
                                <p class="mt-1 font-black text-zinc-900">{{ $part->is_published ? 'Publiée' : 'Non publiée' }}</p>
                            </div>
                        </div>
                    </section>

                    <form method="POST" action="{{ route('scrapyard.parts.preparation.update', $part) }}" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="name" class="text-sm font-black text-zinc-900">Nom de la pièce</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name', $part->name) }}"
                                    required
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
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
                                    value="{{ old('category', $part->category) }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
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
                                    value="{{ old('price', $part->price) }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
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
                                        <option value="{{ $condition }}" @selected(old('condition', $part->condition) === $condition)>
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
                                        <option value="{{ $status }}" @selected(old('status', $part->status) === $status)>
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
                                    value="{{ old('reference', $part->reference) }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
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
                                    value="{{ old('oem_reference', $part->oem_reference) }}"
                                    class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
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
                                >{{ old('description', $part->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                        >
                            Enregistrer la préparation
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </body>
</html>
