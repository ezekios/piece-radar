<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Crédits photos - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $credits = [
                [
                    'id' => 6,
                    'part' => 'Feu arrière gauche',
                    'author' => 'Arivumathi',
                    'license' => 'CC0 1.0',
                    'source' => 'https://commons.wikimedia.org/wiki/File:%22car_tail_lamp.jpg',
                ],
                [
                    'id' => 8,
                    'part' => 'Phare avant gauche',
                    'author' => 'Prabhupuducherry',
                    'license' => 'CC BY-SA 3.0',
                    'source' => 'https://commons.wikimedia.org/wiki/File:Car_headlamp.JPG',
                ],
                [
                    'id' => 10,
                    'part' => 'Capot',
                    'author' => 'Ken30684',
                    'license' => 'CC BY 2.0',
                    'source' => 'https://commons.wikimedia.org/wiki/File:NESN_car_hood.jpg',
                ],
                [
                    'id' => 11,
                    'part' => 'Rétroviseur droit',
                    'author' => 'Johan',
                    'license' => 'CC BY-SA 3.0',
                    'source' => 'https://commons.wikimedia.org/wiki/File:Car-SideMirror.jpg',
                ],
                [
                    'id' => 2,
                    'part' => 'Rétroviseur gauche',
                    'author' => 'Johan',
                    'license' => 'CC BY-SA 3.0',
                    'source' => 'https://commons.wikimedia.org/wiki/File:Car-SideMirror.jpg',
                ],
                [
                    'id' => 5,
                    'part' => 'Aile avant gauche',
                    'author' => 'Chris Light',
                    'license' => 'CC BY-SA 4.0',
                    'source' => 'https://commons.wikimedia.org/wiki/File:Front_Fender_1543.jpg',
                ],
                [
                    'id' => 7,
                    'part' => 'Démarreur',
                    'author' => 'Nadinviki',
                    'license' => 'CC BY-SA 4.0',
                    'source' => 'https://commons.wikimedia.org/wiki/File:MOTOR_STARTER.jpg',
                ],
                [
                    'id' => 9,
                    'part' => 'Compresseur de climatisation',
                    'author' => 'Hd207',
                    'license' => 'CC0 1.0',
                    'source' => 'https://commons.wikimedia.org/wiki/File:MHI_AC_COMPRESSOR_(Minica_H42V).jpg',
                ],
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 py-5 sm:px-6 sm:py-8 lg:px-8">
            <div class="mx-auto w-full max-w-4xl">
                <header class="border-b border-zinc-200/80 pb-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain sm:h-12 sm:max-w-[180px]" />

                        <a href="{{ route('home') }}" class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                            Retour à l’accueil
                        </a>
                    </div>

                    <h1 class="mt-5 text-3xl font-black leading-tight text-zinc-950 sm:text-4xl">
                        Crédits photos
                    </h1>

                    <p class="mt-3 max-w-2xl text-base font-medium leading-7 text-zinc-600">
                        Les photographies utilisées dans cette version de démonstration servent uniquement d’illustration.
                    </p>
                </header>

                <section class="mt-5 rounded-2xl border border-orange-100 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-base font-black text-zinc-950">Mention importante</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-zinc-700">
                        Ces images ne représentent pas nécessairement les pièces exactes provenant des véhicules donneurs enregistrés dans Pièce Radar.
                    </p>
                </section>

                <section class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($credits as $credit)
                        <article class="flex min-h-64 flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
                            <h2 class="text-lg font-black leading-tight text-zinc-950">{{ $credit['part'] }}</h2>

                            <dl class="mt-4 flex-1 space-y-3 text-sm">
                                <div>
                                    <dt class="font-bold text-zinc-500">Auteur</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $credit['author'] }}</dd>
                                </div>

                                <div>
                                    <dt class="font-bold text-zinc-500">Licence</dt>
                                    <dd class="mt-1 font-black text-zinc-900">{{ $credit['license'] }}</dd>
                                </div>
                            </dl>

                            <a
                                href="{{ $credit['source'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-[#FC8505]/30 bg-white px-4 py-3 text-center text-sm font-black text-[#FC8505] transition hover:bg-[#FC8505]/10 focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                            >
                                Voir la source sur Wikimedia Commons
                            </a>
                        </article>
                    @endforeach
                </section>

                <footer class="mt-8 border-t border-zinc-200/80 py-5">
                    <div class="flex flex-col gap-2 text-sm font-medium text-zinc-600 sm:flex-row sm:items-center sm:justify-between">
                        <p>Pièce Radar</p>
                        <a href="{{ route('home') }}" class="font-black text-[#FC8505] hover:text-[#E87804]">
                            Retour à l’accueil
                        </a>
                    </div>
                </footer>
            </div>
        </main>
    </body>
</html>
