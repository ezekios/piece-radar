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
                    <div>
                        <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                        @include('scrapyard.partials.navigation')
                        <a href="{{ route('scrapyard.parts.show', $part) }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                            Retour vers la pièce
                        </a>
                        <h1 class="mt-4 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Préparer la pièce</h1>
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

                    <form method="POST" action="{{ route('scrapyard.parts.preparation.update', $part) }}" enctype="multipart/form-data" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
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

                        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <label for="photos" class="text-sm font-black text-zinc-900">Photos de la pièce</label>
                                    <p class="mt-1 text-xs font-medium leading-5 text-zinc-500">
                                        Maximum 5 photos au total — JPG, PNG ou WebP — 5 Mo maximum par photo.
                                    </p>
                                </div>

                                <span class="rounded-full bg-zinc-50 px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                                    {{ $part->images->count() }}/5
                                </span>
                            </div>

                            @if ($part->images->isNotEmpty())
                                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    @foreach ($part->images as $image)
                                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-2">
                                            <img src="{{ $image->url }}" alt="Photo pièce {{ $loop->iteration }}" class="aspect-[4/3] w-full rounded-lg object-cover">
                                            <button type="submit" form="delete-part-image-{{ $image->id }}" class="mt-2 cursor-pointer text-xs font-black text-[#FC8505] hover:text-[#E87804]">
                                                Supprimer
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($part->images->count() < 5)
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
                            Enregistrer la préparation
                        </button>
                    </form>

                    @foreach ($part->images as $image)
                        <form id="delete-part-image-{{ $image->id }}" method="POST" action="{{ route('scrapyard.parts.images.destroy', [$part, $image]) }}">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                </div>
            </div>
        </main>
    </body>
</html>
