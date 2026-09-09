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

<x-layouts.scrapyard title="Ajouter une pièce - Pièce Radar" max-width="max-w-5xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Pièces"
            title="Ajouter une pièce"
            description="{{ $scrapyard?->name ? 'Renseignez une nouvelle pièce issue du véhicule donneur.' : 'Le formulaire sera disponible dès qu’une casse existera.' }}"
        >
            <x-slot:actions>
                <x-ui.button href="{{ route('scrapyard.vehicles.show', $vehicle) }}" variant="secondary" size="md" class="w-full sm:w-auto">
                    Retour vers le véhicule
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if ($scrapyard)
            <p class="mt-2 text-sm font-semibold text-zinc-500">
                {{ $scrapyard->name }}@if ($scrapyard->city) · {{ $scrapyard->city }}@endif
            </p>
        @endif
    </x-slot:header>

    @if (! $scrapyard)
        <x-ui.empty-state
            class="mt-6"
            title="Aucune casse n’est disponible."
            description="Le formulaire sera disponible dès qu’une casse existera en base."
        />
    @else
        <div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,0.42fr)_minmax(0,0.58fr)]">
            <x-ui.card as="section" padding="p-4 sm:p-5" class="h-fit">
                <p class="text-xs font-black uppercase tracking-[0.12em] text-[#C96504]">Véhicule concerné</p>
                <h2 class="mt-2 break-words text-xl font-black text-zinc-950">
                    {{ $vehicle->brand }} {{ $vehicle->model }}
                </h2>
                <dl class="mt-4 grid gap-3 text-sm min-[390px]:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="text-xs font-bold text-zinc-500">Année</dt>
                        <dd class="mt-1 font-black text-zinc-950">{{ $vehicle->year ?: 'Année non renseignée' }}</dd>
                    </div>
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="text-xs font-bold text-zinc-500">Plaque</dt>
                        <dd class="mt-1 break-words font-black text-zinc-950">{{ $vehicle->license_plate ?: 'Non renseignée' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card padding="p-4 sm:p-5">
                <form method="POST" action="{{ route('scrapyard.vehicles.parts.store', $vehicle) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <section>
                        <h2 class="text-lg font-black text-zinc-950">Informations de la pièce</h2>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-ui.input
                                    id="name"
                                    name="name"
                                    label="Nom de la pièce"
                                    value="{{ old('name') }}"
                                    required
                                    placeholder="Phare avant droit"
                                />
                            </div>

                            <x-ui.input
                                id="category"
                                name="category"
                                label="Catégorie"
                                value="{{ old('category') }}"
                                placeholder="Optique"
                            />

                            <x-ui.input
                                id="price"
                                name="price"
                                type="number"
                                label="Prix"
                                value="{{ old('price') }}"
                                min="0"
                                step="0.01"
                                placeholder="85"
                            />

                            <x-ui.select
                                id="condition"
                                name="condition"
                                label="État"
                                value="{{ old('condition', 'unknown') }}"
                                :options="$conditionLabels"
                            />

                            <x-ui.select
                                id="status"
                                name="status"
                                label="Statut"
                                value="{{ old('status', 'preparing') }}"
                                :options="$statusLabels"
                            />

                            <x-ui.input
                                id="reference"
                                name="reference"
                                label="Référence"
                                value="{{ old('reference') }}"
                                placeholder="REF-123"
                            />

                            <x-ui.input
                                id="oem_reference"
                                name="oem_reference"
                                label="Référence OEM"
                                value="{{ old('oem_reference') }}"
                                placeholder="OEM-456"
                            />

                            <div class="sm:col-span-2">
                                <x-ui.textarea
                                    id="description"
                                    name="description"
                                    label="Description"
                                    value="{{ old('description') }}"
                                    rows="5"
                                    placeholder="Informations utiles sur l’état ou la compatibilité."
                                />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <label for="photos" class="text-sm font-black text-zinc-900">Photos de la pièce</label>
                        <p class="mt-1 text-xs font-medium leading-5 text-zinc-500">
                            Maximum 5 photos — JPG, PNG ou WebP — 5 Mo maximum par photo.
                        </p>
                        <input
                            id="photos"
                            name="photos[]"
                            type="file"
                            multiple
                            accept="image/*"
                            class="mt-3 block w-full cursor-pointer text-sm font-medium text-zinc-700 file:mr-4 file:cursor-pointer file:rounded-xl file:border-0 file:bg-[#FC8505] file:px-4 file:py-2.5 file:text-sm file:font-black file:text-white hover:file:bg-[#E87804]"
                        >
                        @error('photos')
                            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                        @error('photos.*')
                            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </section>

                    <div class="flex flex-col gap-2 min-[390px]:flex-row min-[390px]:items-center">
                        <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full min-[390px]:w-auto">
                            Ajouter la pièce
                        </x-ui.button>

                        <x-ui.button href="{{ route('scrapyard.vehicles.show', $vehicle) }}" variant="secondary" size="lg" class="w-full min-[390px]:w-auto">
                            Annuler
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    @endif
</x-layouts.scrapyard>
