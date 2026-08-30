<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Détail de la pièce - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $vehicle = $part->vehicle;
            $requestScrapyard = $vehicle?->scrapyard;

            $statusLabels = [
                'available' => 'Disponible',
                'reserved' => 'Mise de côté',
                'preparing' => 'En préparation',
                'sold' => 'Vendue',
                'unavailable' => 'Non disponible',
            ];

            $statusClasses = [
                'available' => 'bg-emerald-50 text-emerald-700',
                'reserved' => 'bg-[#FC8505]/10 text-[#C96504]',
                'preparing' => 'bg-amber-50 text-amber-700',
                'sold' => 'bg-blue-50 text-blue-700',
                'unavailable' => 'bg-zinc-100 text-zinc-600',
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
                @if (session('success'))
                    <div class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700 shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <header class="border-b border-zinc-200/80 pb-4">
                    <a href="{{ route('scrapyard.parts.index') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour vers les pièces
                    </a>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                            <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                                Détail de la pièce
                            </h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $requestScrapyard?->name ?? $scrapyard?->name ?? 'Casse non renseignée' }}
                                @if ($requestScrapyard?->city ?? $scrapyard?->city)
                                    · {{ $requestScrapyard?->city ?? $scrapyard->city }}
                                @endif
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$part->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                            {{ $statusLabels[$part->status] ?? $part->status }}
                        </span>
                    </div>
                </header>

                <div class="mt-4 space-y-3">
                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="text-xl font-black text-zinc-950">{{ $part->name }}</h2>
                                <p class="mt-2 text-sm font-medium text-zinc-600">
                                    {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-3xl font-black text-[#FC8505]">
                                    @if ($part->price !== null)
                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                    @else
                                        Prix sur demande
                                    @endif
                                </p>
                                <p class="mt-1 text-xs font-black text-zinc-500">
                                    {{ $part->is_published ? 'Publiée' : 'Non publiée' }}
                                </p>
                            </div>
                        </div>

                        <dl class="mt-4 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm sm:grid-cols-2">
                            @if ($part->reference)
                                <div>
                                    <dt class="font-medium text-zinc-500">Référence</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $part->reference }}</dd>
                                </div>
                            @endif

                            @if ($part->oem_reference)
                                <div>
                                    <dt class="font-medium text-zinc-500">Référence OEM</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $part->oem_reference }}</dd>
                                </div>
                            @endif

                            <div>
                                <dt class="font-medium text-zinc-500">Créée le</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $part->created_at?->format('d/m/Y à H:i') }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Mise à jour le</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $part->updated_at?->format('d/m/Y à H:i') }}</dd>
                            </div>
                        </dl>

                        @if ($part->description)
                            <div class="mt-4 rounded-xl border border-zinc-100 bg-white p-3">
                                <p class="text-xs font-bold text-zinc-500">Description</p>
                                <p class="mt-1 text-sm leading-6 text-zinc-700">{{ $part->description }}</p>
                            </div>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Véhicule associé</h2>

                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
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

                            @if ($vehicle?->engine)
                                <div>
                                    <dt class="font-medium text-zinc-500">Motorisation</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->engine }}</dd>
                                </div>
                            @endif

                            @if ($vehicle?->fuel)
                                <div>
                                    <dt class="font-medium text-zinc-500">Carburant</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->fuel }}</dd>
                                </div>
                            @endif

                            @if ($vehicle?->mileage)
                                <div>
                                    <dt class="font-medium text-zinc-500">Kilométrage</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ number_format($vehicle->mileage, 0, ',', ' ') }} km</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Technique</h2>

                        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="font-medium text-zinc-500">ID pièce</dt>
                                <dd class="mt-1 font-black text-zinc-900">#{{ $part->id }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">ID véhicule</dt>
                                <dd class="mt-1 font-black text-zinc-900">#{{ $part->vehicle_id }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Statut brut</dt>
                                <dd class="mt-1 font-black text-zinc-900">({{ $part->status }})</dd>
                            </div>
                        </dl>
                    </section>

                    @if (! $part->is_published || $part->status !== 'available')
                        <section class="rounded-2xl border border-[#FC8505]/20 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-base font-black text-zinc-950">Publication côté client</h2>
                                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                                        Publier la pièce la rendra disponible dans les résultats client.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('scrapyard.parts.publish', $part) }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto"
                                    >
                                        Publier la pièce
                                    </button>
                                </form>
                            </div>
                        </section>
                    @endif

                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Mise à jour du statut</h2>

                        <form method="POST" action="{{ route('scrapyard.parts.updateStatus', $part) }}" class="mt-4 space-y-3">
                            @csrf

                            <div>
                                <label for="status" class="text-sm font-black text-zinc-900">Nouveau statut</label>
                                <select
                                    id="status"
                                    name="status"
                                    required
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

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                            >
                                Mettre à jour le statut
                            </button>
                        </form>
                    </section>
                </div>
            </div>
        </main>
    </body>
</html>
