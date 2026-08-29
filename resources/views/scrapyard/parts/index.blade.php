<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Pièces de la casse - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $statusLabels = [
                'available' => 'Disponible',
                'reserved' => 'Mise de côté',
                'sold' => 'Vendue',
                'unavailable' => 'Non disponible',
                'preparing' => 'En préparation',
            ];

            $statusClasses = [
                'available' => 'bg-emerald-50 text-emerald-700',
                'reserved' => 'bg-[#FC8505]/10 text-[#C96504]',
                'sold' => 'bg-blue-50 text-blue-700',
                'unavailable' => 'bg-zinc-100 text-zinc-600',
                'preparing' => 'bg-amber-50 text-amber-700',
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
                <header class="border-b border-zinc-200/80 pb-4">
                    <a href="{{ route('scrapyard.dashboard') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Retour au tableau de bord
                    </a>

                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                            <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Pièces de la casse</h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $scrapyard?->name ?? 'Aucune casse disponible' }}
                                @if ($scrapyard?->city)
                                    · {{ $scrapyard->city }}
                                @endif
                            </p>
                        </div>

                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                            {{ $parts->count() }} pièce{{ $parts->count() > 1 ? 's' : '' }} affichée{{ $parts->count() > 1 ? 's' : '' }}
                        </span>
                    </div>
                </header>

                @if (! $scrapyard)
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune casse n’est disponible.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            La liste des pièces s’affichera dès qu’une casse existera en base.
                        </p>
                    </section>
                @else
                    <form method="GET" action="{{ route('scrapyard.parts.index') }}" class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="grid gap-3 sm:grid-cols-[1fr_220px]">
                            <div>
                                <label for="q" class="text-xs font-black text-zinc-700">Rechercher une pièce</label>
                                <input
                                    id="q"
                                    name="q"
                                    type="search"
                                    value="{{ request('q') }}"
                                    placeholder="Nom, référence, marque, modèle..."
                                    class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                >
                            </div>

                            <div>
                                <label for="status" class="text-xs font-black text-zinc-700">Statut</label>
                                <select
                                    id="status"
                                    name="status"
                                    class="mt-1.5 h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                >
                                    <option value="">Tous les statuts</option>
                                    @foreach ($statusLabels as $status => $label)
                                        <option value="{{ $status }}" @selected(request('status') === $status)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <button
                                type="submit"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-[#FC8505] px-5 text-sm font-black text-white transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                            >
                                Rechercher
                            </button>

                            <a href="{{ route('scrapyard.parts.index') }}" class="inline-flex h-11 items-center justify-center text-sm font-bold text-zinc-500 hover:text-zinc-800">
                                Réinitialiser
                            </a>
                        </div>
                    </form>

                    @if ($parts->isEmpty())
                        <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                            <h2 class="text-base font-black text-zinc-950">Aucune pièce trouvée pour le moment.</h2>
                        </section>
                    @else
                        <section class="mt-5 space-y-3">
                            @foreach ($parts as $part)
                                @php
                                    $vehicle = $part->vehicle;
                                    $status = $part->status;
                                @endphp

                                <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h2 class="truncate text-base font-black text-zinc-950">
                                                {{ $part->name }}
                                            </h2>
                                            <p class="mt-1 truncate text-xs font-semibold text-zinc-700">
                                                {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                                @if ($vehicle?->year)
                                                    · {{ $vehicle->year }}
                                                @endif
                                            </p>
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

                                    <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3">
                                        <p class="text-xs font-medium text-zinc-400">
                                            Créée le {{ $part->created_at?->format('d/m/Y à H:i') }}
                                        </p>

                                        <a href="{{ route('scrapyard.parts.show', $part) }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                                            Voir la pièce
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </section>
                    @endif
                @endif
            </div>
        </main>
    </body>
</html>
