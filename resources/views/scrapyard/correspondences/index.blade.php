<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Correspondances - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $displayTimezone = config('app.display_timezone', 'UTC');
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-4xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="mb-4 flex items-center justify-between">
                        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200">
                            Espace casse
                        </span>
                    </div>

                    @include('scrapyard.partials.navigation')

                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Correspondances</h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $correspondences->count() }} opportunité{{ $correspondences->count() > 1 ? 's' : '' }} détectée{{ $correspondences->count() > 1 ? 's' : '' }} pour {{ $scrapyard->name }}
                            </p>
                        </div>
                    </div>
                </header>

                <section class="mt-4 rounded-2xl border border-orange-100 bg-white p-4 shadow-sm">
                    <p class="text-sm font-bold leading-6 text-zinc-700">
                        Un client recherche une pièce correspondant à votre stock. Aucune demande de réservation n’est créée automatiquement.
                    </p>
                </section>

                @if ($correspondences->isEmpty())
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune correspondance pour le moment.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Les besoins clients compatibles avec vos pièces publiées apparaîtront ici.
                        </p>
                    </section>
                @else
                    <section class="mt-5 space-y-3">
                        @foreach ($correspondences as $correspondence)
                            @php
                                $matchedPart = $correspondence->matchedPart;
                                $vehicle = $matchedPart?->vehicle;
                                $matchedAtDisplay = $correspondence->matched_at?->copy()->timezone($displayTimezone);
                                $partImage = $matchedPart?->images->first();
                            @endphp

                            <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                                <div class="flex gap-3">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                                        @if ($partImage)
                                            <img src="{{ $partImage->url }}" alt="Photo {{ $matchedPart->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="h-10 w-14 rounded-md border border-[#FC8505]/50 bg-white shadow-inner"></div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h2 class="text-base font-black text-zinc-950">{{ $correspondence->part_name }}</h2>
                                                <p class="mt-1 text-sm font-semibold text-zinc-700">
                                                    {{ $correspondence->vehicle_brand }} {{ $correspondence->vehicle_model }}
                                                    @if ($correspondence->vehicle_year)
                                                        · {{ $correspondence->vehicle_year }}
                                                    @endif
                                                </p>
                                            </div>

                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                                                Correspondance trouvée
                                            </span>
                                        </div>

                                        <div class="mt-3 rounded-xl bg-zinc-50 p-3 text-sm">
                                            <p class="text-xs font-bold text-zinc-500">Pièce correspondante</p>
                                            <p class="mt-1 font-black text-zinc-950">{{ $matchedPart?->name ?? 'Pièce non renseignée' }}</p>
                                            <p class="mt-1 text-xs font-medium text-zinc-600">
                                                {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                                @if ($vehicle?->year)
                                                    · {{ $vehicle->year }}
                                                @endif
                                            </p>
                                        </div>

                                        <div class="mt-3 flex flex-col gap-2 border-t border-zinc-100 pt-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-xs font-bold text-zinc-500">Date de détection</p>
                                                <p class="mt-1 text-sm font-black text-zinc-950">
                                                    {{ $matchedAtDisplay?->format('d/m/Y à H:i') ?? 'Non renseignée' }}
                                                </p>
                                            </div>

                                            @if ($matchedPart)
                                                <a href="{{ route('scrapyard.parts.show', $matchedPart) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-4 py-2 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                                    Voir la pièce
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>
        </main>
    </body>
</html>
