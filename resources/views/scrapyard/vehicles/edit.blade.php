<x-layouts.scrapyard title="Modifier le véhicule - Pièce Radar" max-width="max-w-5xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Véhicules"
            title="Modifier le véhicule"
            description="{{ $scrapyard->name }}{{ $scrapyard->city ? ' · '.$scrapyard->city : '' }}"
        >
            <x-slot:actions>
                <x-ui.button href="{{ route('scrapyard.vehicles.show', $vehicle) }}" variant="secondary" size="md" class="w-full sm:w-auto">
                    Retour vers le véhicule
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot:header>

    <x-ui.card class="mt-6" padding="p-4 sm:p-5">
        <form method="POST" action="{{ route('scrapyard.vehicles.update', $vehicle) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <section>
                <h2 class="text-lg font-black text-zinc-950">Informations du véhicule</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.input
                        id="brand"
                        name="brand"
                        label="Marque"
                        value="{{ $vehicle->brand }}"
                        required
                        placeholder="Renault"
                    />

                    <x-ui.input
                        id="model"
                        name="model"
                        label="Modèle"
                        value="{{ $vehicle->model }}"
                        required
                        placeholder="Clio IV"
                    />

                    <x-ui.input
                        id="year"
                        name="year"
                        type="number"
                        label="Année"
                        value="{{ $vehicle->year }}"
                        min="1900"
                        max="{{ (int) date('Y') + 1 }}"
                        placeholder="2017"
                    />

                    <x-ui.input
                        id="license_plate"
                        name="license_plate"
                        label="Plaque d’immatriculation"
                        value="{{ $vehicle->license_plate }}"
                        placeholder="AB-123-CD"
                        class="uppercase placeholder:normal-case"
                    />

                    <x-ui.input
                        id="fuel"
                        name="fuel"
                        label="Carburant"
                        value="{{ $vehicle->fuel }}"
                        placeholder="Diesel"
                    />

                    <x-ui.input
                        id="engine"
                        name="engine"
                        label="Motorisation"
                        value="{{ $vehicle->engine }}"
                        placeholder="1.5 dCi"
                    />

                    <div class="sm:col-span-2">
                        <x-ui.input
                            id="mileage"
                            name="mileage"
                            type="number"
                            label="Kilométrage"
                            value="{{ $vehicle->mileage }}"
                            min="0"
                            placeholder="125000"
                        />
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <label for="photos" class="text-sm font-black text-zinc-900">Photos du véhicule</label>
                        <p class="mt-1 text-xs font-medium leading-5 text-zinc-500">
                            Maximum 5 photos au total — JPG, PNG ou WebP — 5 Mo maximum par photo.
                        </p>
                    </div>

                    <x-ui.badge variant="neutral">{{ $vehicle->images->count() }}/5</x-ui.badge>
                </div>

                @if ($vehicle->images->isNotEmpty())
                    <div class="mt-4 grid grid-cols-2 gap-3 min-[430px]:grid-cols-3">
                        @foreach ($vehicle->images as $image)
                            <div class="rounded-xl border border-zinc-200 bg-white p-2">
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
            </section>

            <div class="flex flex-col gap-2 min-[390px]:flex-row min-[390px]:items-center">
                <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full min-[390px]:w-auto">
                    Enregistrer les modifications
                </x-ui.button>

                <x-ui.button href="{{ route('scrapyard.vehicles.show', $vehicle) }}" variant="secondary" size="lg" class="w-full min-[390px]:w-auto">
                    Annuler
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @foreach ($vehicle->images as $image)
        <form id="delete-vehicle-image-{{ $image->id }}" method="POST" action="{{ route('scrapyard.vehicles.images.destroy', [$vehicle, $image]) }}">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</x-layouts.scrapyard>
