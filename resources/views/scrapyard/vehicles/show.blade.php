<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Détail du véhicule - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $statusLabels = [
                'available' => 'Disponible',
                'preparing' => 'En préparation',
                'reserved' => 'Mise de côté',
                'sold' => 'Vendue',
                'unavailable' => 'Non disponible',
            ];

            $statusClasses = [
                'available' => 'bg-emerald-50 text-emerald-700',
                'preparing' => 'bg-amber-50 text-amber-700',
                'reserved' => 'bg-[#FC8505]/10 text-[#C96504]',
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
            <div class="mx-auto w-full max-w-4xl">
                @if (session('success'))
                    <div class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700 shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <header class="border-b border-zinc-200/80 pb-4">
                    <a href="{{ route('scrapyard.vehicles.index') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour vers les véhicules
                    </a>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                            <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                                Détail du véhicule
                            </h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $scrapyard->name }}
                                @if ($scrapyard->city)
                                    · {{ $scrapyard->city }}
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                                {{ $vehicle->parts->count() }} pièce{{ $vehicle->parts->count() > 1 ? 's' : '' }}
                            </span>

                            <a href="{{ route('scrapyard.vehicles.parts.create', $vehicle) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                Ajouter une pièce
                            </a>
                        </div>
                    </div>
                </header>

                <div class="mt-4 space-y-3">
                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-black text-zinc-950">
                                    {{ $vehicle->brand }} {{ $vehicle->model }}
                                </h2>
                                <p class="mt-2 text-sm font-medium text-zinc-600">
                                    {{ $vehicle->license_plate ?: 'Immatriculation non renseignée' }}
                                </p>
                            </div>

                            @if ($vehicle->year)
                                <span class="rounded-full bg-[#FC8505]/10 px-3 py-1 text-xs font-black text-[#C96504]">
                                    {{ $vehicle->year }}
                                </span>
                            @endif
                        </div>

                        <dl class="mt-4 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="font-medium text-zinc-500">Marque</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->brand }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Modèle</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->model }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Année</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->year ?? 'Non renseignée' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Carburant</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->fuel ?: 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Motorisation</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->engine ?: 'Non renseignée' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Kilométrage</dt>
                                <dd class="mt-1 font-black text-zinc-900">
                                    @if ($vehicle->mileage)
                                        {{ number_format($vehicle->mileage, 0, ',', ' ') }} km
                                    @else
                                        Non renseigné
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Date d’ajout</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->created_at?->format('d/m/Y à H:i') }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-zinc-500">Mise à jour</dt>
                                <dd class="mt-1 font-black text-zinc-900">{{ $vehicle->updated_at?->format('d/m/Y à H:i') }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h2 class="text-lg font-black text-zinc-950">Pièces associées</h2>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-500 ring-1 ring-zinc-200">
                                {{ $vehicle->parts->count() }}
                            </span>
                        </div>

                        @if ($vehicle->parts->isEmpty())
                            <div class="rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                                <h3 class="text-base font-black text-zinc-950">Aucune pièce associée à ce véhicule pour le moment.</h3>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($vehicle->parts as $part)
                                    @php
                                        $status = $part->status;
                                    @endphp

                                    <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="truncate text-base font-black text-zinc-950">
                                                    {{ $part->name }}
                                                </h3>
                                                <p class="mt-1 text-xs font-medium text-zinc-500">
                                                    {{ $conditionLabels[$part->condition] ?? $part->condition ?? 'État non précisé' }}
                                                </p>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-xl font-black text-[#FC8505]">
                                                    @if ($part->price !== null)
                                                        {{ number_format((float) $part->price, 2, ',', ' ') }} €
                                                    @else
                                                        Prix sur demande
                                                    @endif
                                                </p>
                                                <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                                    {{ $statusLabels[$status] ?? $status }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-3 rounded-xl bg-zinc-50 p-3 text-sm sm:grid-cols-3">
                                            <div>
                                                <p class="text-xs font-bold text-zinc-500">Publication</p>
                                                <p class="mt-1 font-black text-zinc-950">
                                                    {{ $part->is_published ? 'Publiée' : 'Non publiée' }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-xs font-bold text-zinc-500">Référence</p>
                                                <p class="mt-1 font-black text-zinc-950">{{ $part->reference ?: 'Non renseignée' }}</p>
                                            </div>

                                            <div>
                                                <p class="text-xs font-bold text-zinc-500">Référence OEM</p>
                                                <p class="mt-1 font-black text-zinc-950">{{ $part->oem_reference ?: 'Non renseignée' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex justify-end border-t border-zinc-100 pt-3">
                                            <a href="{{ route('scrapyard.parts.show', $part) }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                                Voir la pièce
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>
            </div>
        </main>
    </body>
</html>
