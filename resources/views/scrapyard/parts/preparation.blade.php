@php
    $vehicle = $part->vehicle;
    $requestScrapyard = $vehicle?->scrapyard;
    $scrapyardName = $requestScrapyard?->name ?? $scrapyard?->name ?? 'Casse non renseignée';
    $scrapyardCity = $requestScrapyard?->city ?? $scrapyard?->city;
    $headerDescription = $scrapyardName . ($scrapyardCity ? ' · ' . $scrapyardCity : '');

    $statusLabels = [
        'preparing' => 'En préparation',
        'available' => 'Disponible',
        'reserved' => 'Mise de côté',
        'sold' => 'Vendue',
        'unavailable' => 'Non disponible',
    ];

    $statusVariants = [
        'preparing' => 'orange',
        'available' => 'success',
        'reserved' => 'orange',
        'sold' => 'info',
        'unavailable' => 'neutral',
    ];

    $conditionLabels = [
        'unknown' => 'État non précisé',
        'used_good' => 'Occasion bon état',
        'used_average' => 'Occasion état moyen',
        'damaged' => 'Endommagée',
    ];
@endphp

<x-layouts.scrapyard title="Préparer la pièce - Pièce Radar" max-width="max-w-6xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Préparation"
            title="Préparer la pièce"
            :description="$headerDescription"
        >
            <x-slot:actions>
                <x-ui.badge :variant="$statusVariants[$part->status] ?? 'neutral'">
                    {{ $statusLabels[$part->status] ?? $part->status }}
                </x-ui.badge>
                <x-ui.badge :variant="$part->is_published ? 'success' : 'neutral'">
                    {{ $part->is_published ? 'Publiée' : 'Non publiée' }}
                </x-ui.badge>
            </x-slot:actions>
        </x-ui.page-header>

        <a href="{{ route('scrapyard.parts.show', $part) }}" class="mt-4 inline-flex text-sm font-black text-[#FC8505] hover:text-[#E87804]">
            Retour vers la pièce
        </a>
    </x-slot:header>

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,0.36fr)_minmax(0,0.64fr)]">
        <aside class="space-y-5">
            <x-ui.card as="section" padding="p-4 sm:p-5">
                <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Véhicule associé</p>

                <dl class="mt-4 grid gap-3 text-sm min-[390px]:grid-cols-3 xl:grid-cols-1">
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Marque</dt>
                        <dd class="mt-1 break-words font-black text-zinc-900">{{ $vehicle?->brand ?? 'Non renseignée' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Modèle</dt>
                        <dd class="mt-1 break-words font-black text-zinc-900">{{ $vehicle?->model ?? 'Non renseigné' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="font-medium text-zinc-500">Année</dt>
                        <dd class="mt-1 font-black text-zinc-900">{{ $vehicle?->year ?? 'Non renseignée' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card as="section" padding="p-4 sm:p-5">
                <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Pièce à vérifier</p>

                <div class="mt-4 space-y-3">
                    <div>
                        <p class="text-sm font-medium text-zinc-500">Nom</p>
                        <p class="mt-1 break-words text-lg font-black text-zinc-950">{{ $part->name }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge :variant="$statusVariants[$part->status] ?? 'neutral'">
                            {{ $statusLabels[$part->status] ?? $part->status }}
                        </x-ui.badge>
                        <x-ui.badge :variant="$part->is_published ? 'success' : 'neutral'">
                            {{ $part->is_published ? 'Publiée' : 'Non publiée' }}
                        </x-ui.badge>
                    </div>
                </div>
            </x-ui.card>
        </aside>

        <x-ui.card as="section" padding="p-4 sm:p-6">
            <div class="flex flex-col gap-2 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                <div>
                    <h2 class="text-xl font-black text-zinc-950">Formulaire de vérification</h2>
                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                        Mettez à jour les informations principales avant publication côté client.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('scrapyard.parts.preparation.update', $part) }}" enctype="multipart/form-data" class="mt-5 space-y-5">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            id="name"
                            name="name"
                            label="Nom de la pièce"
                            value="{{ $part->name }}"
                            required
                        />
                    </div>

                    <x-ui.input
                        id="category"
                        name="category"
                        label="Catégorie"
                        value="{{ $part->category }}"
                    />

                    <x-ui.input
                        id="price"
                        name="price"
                        type="number"
                        label="Prix"
                        value="{{ $part->price }}"
                        min="0"
                        step="0.01"
                    />

                    <x-ui.select
                        id="condition"
                        name="condition"
                        label="État"
                        value="{{ $part->condition }}"
                        :options="$conditionLabels"
                    />

                    <x-ui.select
                        id="status"
                        name="status"
                        label="Statut"
                        value="{{ $part->status }}"
                        :options="$statusLabels"
                    />

                    <x-ui.input
                        id="reference"
                        name="reference"
                        label="Référence"
                        value="{{ $part->reference }}"
                    />

                    <x-ui.input
                        id="oem_reference"
                        name="oem_reference"
                        label="Référence OEM"
                        value="{{ $part->oem_reference }}"
                    />

                    <div class="sm:col-span-2">
                        <x-ui.textarea
                            id="description"
                            name="description"
                            label="Description"
                            :value="$part->description"
                            rows="5"
                        />
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                    <div class="flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                        <div>
                            <label for="photos" class="text-sm font-black text-zinc-900">Photos de la pièce</label>
                            <p class="mt-1 text-xs font-medium leading-5 text-zinc-500">
                                Maximum 5 photos au total. JPG, PNG ou WebP, 5 Mo maximum par photo.
                            </p>
                        </div>

                        <x-ui.badge variant="neutral">{{ $part->images->count() }}/5</x-ui.badge>
                    </div>

                    @if ($part->images->isNotEmpty())
                        <div class="mt-4 grid grid-cols-2 gap-3 min-[390px]:grid-cols-3 lg:grid-cols-4">
                            @foreach ($part->images as $image)
                                <div class="rounded-xl border border-zinc-200 bg-white p-2 shadow-sm">
                                    <img src="{{ $image->url }}" alt="Photo pièce {{ $loop->iteration }}" class="aspect-[4/3] w-full rounded-lg object-cover">
                                    <button type="submit" form="delete-part-image-{{ $image->id }}" class="mt-2 cursor-pointer text-xs font-black text-[#FC8505] transition hover:text-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
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
                        <p class="mt-4 rounded-xl bg-white p-3 text-sm font-bold text-zinc-600 ring-1 ring-zinc-200">
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

                <div class="flex flex-col-reverse gap-2 min-[390px]:flex-row min-[390px]:justify-end">
                    <x-ui.button href="{{ route('scrapyard.parts.show', $part) }}" variant="secondary" size="lg" class="w-full min-[390px]:w-auto">
                        Annuler
                    </x-ui.button>
                    <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full min-[390px]:w-auto">
                        Enregistrer la préparation
                    </x-ui.button>
                </div>
            </form>

            @foreach ($part->images as $image)
                <form id="delete-part-image-{{ $image->id }}" method="POST" action="{{ route('scrapyard.parts.images.destroy', [$part, $image]) }}">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </x-ui.card>
    </div>
</x-layouts.scrapyard>
