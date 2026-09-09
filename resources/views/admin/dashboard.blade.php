<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Administration - Pièce Radar</title>

        <x-ui.theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        @php
            $admin = auth()->user();

            $toneClasses = [
                'orange' => 'bg-[#FC8505]/10 text-[#C96504] ring-orange-100 dark:bg-[#FC8505]/15 dark:text-orange-200 dark:ring-[#FC8505]/30',
                'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-900',
                'info' => 'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-900',
                'neutral' => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 ring-zinc-200 dark:ring-zinc-700',
            ];

            $primaryStats = [
                [
                    'label' => 'Utilisateurs',
                    'value' => $stats['users_total'],
                    'description' => 'Comptes inscrits sur la plateforme.',
                    'url' => route('admin.users.index'),
                    'icon' => 'users',
                    'tone' => 'orange',
                ],
                [
                    'label' => 'Casses',
                    'value' => $stats['scrapyards_total'],
                    'description' => 'Professionnels casse référencés.',
                    'url' => route('admin.scrapyards.index'),
                    'icon' => 'building',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Pièces',
                    'value' => $stats['parts_total'],
                    'description' => 'Pièces enregistrées dans le stock.',
                    'url' => null,
                    'icon' => 'parts',
                    'tone' => 'info',
                ],
                [
                    'label' => 'Demandes',
                    'value' => $stats['requests_total'],
                    'description' => 'Demandes de mise de côté créées.',
                    'url' => null,
                    'icon' => 'requests',
                    'tone' => 'neutral',
                ],
            ];

            $accountStats = [
                ['label' => 'Clients', 'value' => $stats['clients_total']],
                ['label' => 'Professionnels', 'value' => $stats['professionals_total']],
                ['label' => 'Casses', 'value' => $stats['scrapyards_total']],
            ];

            $platformStats = [
                ['label' => 'Véhicules', 'value' => $stats['vehicles_total']],
                ['label' => 'Pièces', 'value' => $stats['parts_total']],
                ['label' => 'Demandes', 'value' => $stats['requests_total']],
                ['label' => 'Recherches sauvegardées', 'value' => $stats['saved_searches_total']],
            ];

            $quickActions = [
                [
                    'label' => 'Voir les utilisateurs',
                    'description' => 'Contrôler les rôles et les comptes inscrits.',
                    'url' => route('admin.users.index'),
                    'icon' => 'users',
                    'primary' => true,
                ],
                [
                    'label' => 'Gérer les casses',
                    'description' => 'Vérifier les SIRET, emails et statuts d’activation.',
                    'url' => route('admin.scrapyards.index'),
                    'icon' => 'building',
                    'primary' => false,
                ],
            ];
        @endphp

        <main class="min-h-screen w-full px-3 pb-8 pt-3 sm:px-6 sm:pb-10 sm:pt-4 md:pl-80 md:pr-6 md:pt-6 lg:pr-8">
            <div class="mx-auto w-full max-w-7xl">
                @include('admin.partials.navigation')

                <header class="mt-4 flex flex-col gap-3 sm:gap-4 md:mt-0 xl:flex-row xl:items-center xl:justify-between">
                    <div class="max-w-3xl">
                        <x-ui.badge variant="orange" class="hidden md:inline-flex">Administrateur</x-ui.badge>
                        <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50 sm:mt-3 sm:text-3xl lg:text-4xl">
                            Tableau de bord admin
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm font-medium leading-5 text-zinc-600 dark:text-zinc-400 sm:mt-2 sm:text-base sm:leading-6">
                            Pilotez les comptes, les casses référencées et l’activité réelle de Pièce Radar.
                        </p>
                    </div>

                    <x-ui.card padding="p-3 sm:p-4" class="xl:min-w-[22rem]">
                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-zinc-400 sm:text-xs sm:tracking-[0.14em]">Compte connecté</p>
                        <p class="mt-1.5 truncate text-sm font-black text-zinc-950 dark:text-zinc-50 sm:mt-2">{{ $admin?->name ?? 'Administrateur' }}</p>
                        <p class="mt-1 truncate text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $admin?->email }}</p>
                    </x-ui.card>
                </header>

                <section class="mt-5 grid grid-cols-1 gap-3 min-[375px]:grid-cols-2 sm:mt-6 sm:gap-4 xl:grid-cols-4" aria-label="Statistiques principales admin">
                    @foreach ($primaryStats as $stat)
                        @php
                            $tone = $toneClasses[$stat['tone']] ?? $toneClasses['neutral'];
                        @endphp

                        @if ($stat['url'])
                            <x-ui.card as="a" href="{{ $stat['url'] }}" interactive padding="p-3 sm:p-5" class="block">
                                <div class="flex items-start justify-between gap-3 sm:gap-4">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl ring-1 sm:h-11 sm:w-11 sm:rounded-2xl {{ $tone }}">
                                        <x-ui.icon :name="$stat['icon']" class="h-4 w-4 sm:h-5 sm:w-5" />
                                    </span>
                                    <x-ui.icon name="arrow" class="h-4 w-4 text-zinc-300" />
                                </div>

                                <p class="mt-3 text-2xl font-black tracking-tight text-zinc-950 dark:text-zinc-50 sm:mt-5 sm:text-3xl">{{ $stat['value'] }}</p>
                                <h2 class="mt-0.5 text-[13px] font-black leading-5 text-zinc-950 dark:text-zinc-50 sm:mt-1 sm:text-sm">{{ $stat['label'] }}</h2>
                                <p class="mt-0.5 text-[11px] font-semibold leading-4 text-zinc-500 dark:text-zinc-400 sm:mt-1 sm:text-xs sm:leading-5">{{ $stat['description'] }}</p>
                            </x-ui.card>
                        @else
                            <x-ui.card as="article" padding="p-3 sm:p-5" class="block">
                                <div class="flex items-start justify-between gap-3 sm:gap-4">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl ring-1 sm:h-11 sm:w-11 sm:rounded-2xl {{ $tone }}">
                                        <x-ui.icon :name="$stat['icon']" class="h-4 w-4 sm:h-5 sm:w-5" />
                                    </span>
                                </div>

                                <p class="mt-3 text-2xl font-black tracking-tight text-zinc-950 dark:text-zinc-50 sm:mt-5 sm:text-3xl">{{ $stat['value'] }}</p>
                                <h2 class="mt-0.5 text-[13px] font-black leading-5 text-zinc-950 dark:text-zinc-50 sm:mt-1 sm:text-sm">{{ $stat['label'] }}</h2>
                                <p class="mt-0.5 text-[11px] font-semibold leading-4 text-zinc-500 dark:text-zinc-400 sm:mt-1 sm:text-xs sm:leading-5">{{ $stat['description'] }}</p>
                            </x-ui.card>
                        @endif
                    @endforeach
                </section>

                <section class="mt-5 grid gap-4 sm:mt-6 sm:gap-5 xl:grid-cols-[minmax(0,1.25fr)_minmax(340px,0.75fr)]">
                    <div class="space-y-4 sm:space-y-5">
                        <x-ui.card padding="p-4 sm:p-5">
                            <div>
                                <h2 class="text-xl font-black text-zinc-950 dark:text-zinc-50">Actions rapides</h2>
                                <p class="mt-1 text-sm font-medium leading-5 text-zinc-600 dark:text-zinc-400 sm:leading-6">
                                    Les écrans d’administration réellement disponibles.
                                </p>
                            </div>

                            <div class="mt-4 grid gap-3 sm:mt-5 md:grid-cols-2">
                                @foreach ($quickActions as $action)
                                    <x-ui.card
                                        as="a"
                                        href="{{ $action['url'] }}"
                                        :variant="$action['primary'] ? 'orange' : 'white'"
                                        interactive
                                        padding="p-3 sm:p-4"
                                        class="block"
                                    >
                                        <div class="flex items-start gap-2.5 sm:gap-3">
                                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl sm:h-10 sm:w-10 sm:rounded-2xl {{ $action['primary'] ? 'bg-white/15 text-white' : 'bg-[#FC8505]/10 text-[#C96504]' }}">
                                                <x-ui.icon :name="$action['icon']" class="h-4 w-4 sm:h-5 sm:w-5" />
                                            </span>

                                            <span class="min-w-0">
                                                <span class="block text-sm font-black {{ $action['primary'] ? 'text-white' : 'text-zinc-950 dark:text-zinc-50' }}">{{ $action['label'] }}</span>
                                                <span class="mt-0.5 block text-xs font-semibold leading-5 {{ $action['primary'] ? 'text-white/85' : 'text-zinc-500 dark:text-zinc-400' }}">
                                                    {{ $action['description'] }}
                                                </span>
                                            </span>
                                        </div>
                                    </x-ui.card>
                                @endforeach
                            </div>
                        </x-ui.card>

                        <div class="grid gap-4 sm:gap-5 lg:grid-cols-2">
                            <x-ui.card padding="p-3 sm:p-5">
                                <h2 class="text-lg font-black text-zinc-950 dark:text-zinc-50">Comptes</h2>
                                <div class="mt-3 space-y-2 sm:mt-4">
                                    @foreach ($accountStats as $item)
                                        <div class="flex min-h-10 items-center justify-between gap-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 py-2 sm:min-h-12">
                                            <span class="text-xs font-black text-zinc-800 dark:text-zinc-200 sm:text-sm">{{ $item['label'] }}</span>
                                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-[#C96504] ring-1 ring-orange-100 dark:bg-zinc-900 dark:ring-[#FC8505]/30">{{ $item['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </x-ui.card>

                            <x-ui.card padding="p-3 sm:p-5">
                                <h2 class="text-lg font-black text-zinc-950 dark:text-zinc-50">Activité plateforme</h2>
                                <div class="mt-3 space-y-2 sm:mt-4">
                                    @foreach ($platformStats as $item)
                                        <div class="flex min-h-10 items-center justify-between gap-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 px-3 py-2 sm:min-h-12">
                                            <span class="text-xs font-black text-zinc-800 dark:text-zinc-200 sm:text-sm">{{ $item['label'] }}</span>
                                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-[#C96504] ring-1 ring-orange-100 dark:bg-zinc-900 dark:ring-[#FC8505]/30">{{ $item['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </x-ui.card>
                        </div>
                    </div>

                    <aside class="space-y-4 sm:space-y-5">
                        <x-ui.card padding="p-4 sm:p-5">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#FC8505]/10 text-[#C96504]">
                                    <x-ui.icon name="shield" class="h-5 w-5" />
                                </span>
                                <div>
                                    <h2 class="text-lg font-black text-zinc-950 dark:text-zinc-50">Contrôle admin</h2>
                                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                                        La validation des casses se fait depuis la page Casses, avec contrôle de vérification email conservé.
                                    </p>
                                </div>
                            </div>

                            <x-ui.button href="{{ route('admin.scrapyards.index') }}" variant="secondary" size="md" class="mt-4 w-full">
                                Gérer les casses
                            </x-ui.button>
                        </x-ui.card>

                    </aside>
                </section>
            </div>
        </main>
    </body>
</html>
