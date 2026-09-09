<x-layouts.scrapyard title="Ajouter un véhicule - Pièce Radar" max-width="max-w-5xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Véhicules"
            title="Ajouter un véhicule"
            description="{{ $scrapyard?->name ? 'Créez une fiche donneur avant d’ajouter les pièces disponibles.' : 'Le formulaire sera disponible dès qu’une casse existera.' }}"
        >
            <x-slot:actions>
                <x-ui.button href="{{ route('scrapyard.vehicles.index') }}" variant="secondary" size="md" class="w-full sm:w-auto">
                    Retour vers les véhicules
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
        <x-ui.card class="mt-6" padding="p-4 sm:p-5">
            <form method="POST" action="{{ route('scrapyard.vehicles.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <section>
                    <h2 class="text-lg font-black text-zinc-950">Informations du véhicule</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-ui.input
                            id="brand"
                            name="brand"
                            label="Marque"
                            value="{{ old('brand') }}"
                            required
                            placeholder="Renault"
                        />

                        <x-ui.input
                            id="model"
                            name="model"
                            label="Modèle"
                            value="{{ old('model') }}"
                            required
                            placeholder="Clio IV"
                        />

                        <x-ui.input
                            id="year"
                            name="year"
                            type="number"
                            label="Année"
                            value="{{ old('year') }}"
                            min="1900"
                            max="{{ (int) date('Y') + 1 }}"
                            placeholder="2017"
                        />

                        <x-ui.input
                            id="license_plate"
                            name="license_plate"
                            label="Plaque d’immatriculation"
                            value="{{ old('license_plate') }}"
                            placeholder="AB-123-CD"
                            class="uppercase placeholder:normal-case"
                        />

                        <x-ui.input
                            id="fuel"
                            name="fuel"
                            label="Carburant"
                            value="{{ old('fuel') }}"
                            placeholder="Diesel"
                        />

                        <x-ui.input
                            id="engine"
                            name="engine"
                            label="Motorisation"
                            value="{{ old('engine') }}"
                            placeholder="1.5 dCi"
                        />

                        <div class="sm:col-span-2">
                            <x-ui.input
                                id="mileage"
                                name="mileage"
                                type="number"
                                label="Kilométrage"
                                value="{{ old('mileage') }}"
                                min="0"
                                placeholder="125000"
                            />
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                    <label for="photos" class="text-sm font-black text-zinc-900">Photos du véhicule</label>
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
                        Ajouter le véhicule
                    </x-ui.button>

                    <x-ui.button href="{{ route('scrapyard.vehicles.index') }}" variant="secondary" size="lg" class="w-full min-[390px]:w-auto">
                        Annuler
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif
</x-layouts.scrapyard>
