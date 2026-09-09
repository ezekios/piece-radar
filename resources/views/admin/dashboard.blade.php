<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Administration - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-6xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="space-y-5">
                <header class="space-y-4 border-b border-zinc-200/80 pb-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />
                        <span class="rounded-full bg-[#FC8505]/10 px-3 py-1 text-xs font-black text-[#C96504] ring-1 ring-[#FC8505]/20">
                            Administrateur
                        </span>
                    </div>

                    @include('admin.partials.navigation')

                    <div>
                        <h1 class="text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Tableau de bord admin</h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            Vue de pilotage minimale de Pièce Radar.
                        </p>
                    </div>
                </header>

                <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        'Utilisateurs' => $stats['users_total'],
                        'Clients' => $stats['clients_total'],
                        'Professionnels' => $stats['professionals_total'],
                        'Casses' => $stats['scrapyards_total'],
                        'Véhicules' => $stats['vehicles_total'],
                        'Pièces' => $stats['parts_total'],
                        'Demandes' => $stats['requests_total'],
                        'Recherches sauvegardées' => $stats['saved_searches_total'],
                    ] as $label => $value)
                        <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-black uppercase text-zinc-500">{{ $label }}</p>
                            <p class="mt-2 text-3xl font-black text-[#FC8505]">{{ $value }}</p>
                        </article>
                    @endforeach
                </section>

                <section class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('admin.users.index') }}" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-orange-200">
                        <p class="text-base font-black text-zinc-950">Utilisateurs</p>
                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">Consulter les comptes inscrits.</p>
                    </a>

                    <a href="{{ route('admin.scrapyards.index') }}" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-orange-200">
                        <p class="text-base font-black text-zinc-950">Casses</p>
                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">Vérifier les casses et leur statut actif.</p>
                    </a>
                </section>
            </div>
        </main>
    </body>
</html>
