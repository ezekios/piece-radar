<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Tableau de bord casse - Pièce Radar</title>

        <x-ui.theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        @php
            $statusLabels = [
                'pending' => 'En attente',
                'accepted' => 'Acceptée',
                'refused' => 'Refusée',
                'cancelled' => 'Annulée',
                'completed' => 'Terminée',
                'expired' => 'Expirée',
            ];

            $statusVariants = [
                'pending' => 'orange',
                'accepted' => 'success',
                'refused' => 'danger',
                'cancelled' => 'neutral',
                'completed' => 'info',
                'expired' => 'orange',
            ];

            $partStatusLabels = [
                'preparing' => 'En préparation',
                'available' => 'Disponible',
                'reserved' => 'Mise de côté',
                'sold' => 'Vendue',
                'unavailable' => 'Non disponible',
            ];

            $displayTimezone = config('app.display_timezone', 'UTC');
            $user = auth()->user();
            $unreadNotificationsCount = $user?->unreadNotifications()->count() ?? 0;
            $scrapyardName = $scrapyard?->name ?? 'Compte casse';
            $scrapyardCity = $scrapyard?->city;

            $toneClasses = [
                'orange' => 'bg-[#FC8505]/10 text-[#C96504] ring-orange-100 dark:bg-[#FC8505]/15 dark:text-orange-200 dark:ring-[#FC8505]/30',
                'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-900',
                'info' => 'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-900',
                'neutral' => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 ring-zinc-200 dark:ring-zinc-700',
            ];

            $primaryStats = [
                [
                    'label' => 'Demandes en attente',
                    'value' => $stats['pending_requests'] ?? 0,
                    'description' => 'Demandes à traiter côté casse.',
                    'url' => route('scrapyard.requests.index', ['status' => 'pending']),
                    'icon' => 'requests',
                    'tone' => 'orange',
                ],
                [
                    'label' => 'Pièces disponibles',
                    'value' => $stats['available_parts'] ?? 0,
                    'description' => 'Stock immédiatement exploitable.',
                    'url' => route('scrapyard.parts.index', ['status' => 'available']),
                    'icon' => 'check',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Pièces publiées',
                    'value' => $stats['publishedPartsCount'] ?? 0,
                    'description' => 'Visibles côté recherche client.',
                    'url' => route('scrapyard.parts.index', ['publication' => 'published']),
                    'icon' => 'package',
                    'tone' => 'info',
                ],
                [
                    'label' => 'Véhicules',
                    'value' => $stats['vehicles_total'] ?? 0,
                    'description' => 'Véhicules donneurs enregistrés.',
                    'url' => route('scrapyard.vehicles.index'),
                    'icon' => 'vehicles',
                    'tone' => 'neutral',
                ],
            ];

            $inventoryStats = [
                ['label' => 'Pièces totales', 'value' => $stats['parts_total'] ?? 0],
                ['label' => 'Disponibles', 'value' => $stats['available_parts'] ?? 0],
                ['label' => 'Mises de côté', 'value' => $stats['reserved_parts'] ?? 0],
                ['label' => 'En préparation', 'value' => $stats['preparingPartsCount'] ?? 0],
                ['label' => 'Publiées', 'value' => $stats['publishedPartsCount'] ?? 0],
                ['label' => 'Non publiées', 'value' => $stats['unpublishedPartsCount'] ?? 0],
            ];

            $requestStats = [
                ['label' => 'Demandes reçues', 'value' => $stats['requests_total'] ?? 0],
                ['label' => 'En attente', 'value' => $stats['pending_requests'] ?? 0],
                ['label' => 'Acceptées', 'value' => $stats['accepted_requests'] ?? 0],
                ['label' => 'Refusées', 'value' => $stats['refused_requests'] ?? 0],
                ['label' => 'Annulées', 'value' => $stats['cancelled_requests'] ?? 0],
                ['label' => 'Terminées', 'value' => $stats['completed_requests'] ?? 0],
                ['label' => 'Expirées', 'value' => $stats['expired_requests'] ?? 0],
            ];

            $quickActions = [
                [
                    'label' => 'Ajouter un véhicule',
                    'description' => 'Créer une fiche donneur et préparer son stock.',
                    'url' => route('scrapyard.vehicles.create'),
                    'icon' => 'plus',
                    'primary' => true,
                ],
                [
                    'label' => 'Voir les véhicules',
                    'description' => 'Gérer le parc de véhicules donneurs.',
                    'url' => route('scrapyard.vehicles.index'),
                    'icon' => 'vehicles',
                    'primary' => false,
                ],
                [
                    'label' => 'Voir les pièces',
                    'description' => 'Préparer, publier ou retirer des pièces.',
                    'url' => route('scrapyard.parts.index'),
                    'icon' => 'parts',
                    'primary' => false,
                ],
                [
                    'label' => 'Voir les demandes reçues',
                    'description' => 'Répondre aux demandes de mise de côté.',
                    'url' => route('scrapyard.requests.index'),
                    'icon' => 'requests',
                    'primary' => false,
                ],
                [
                    'label' => 'Voir les correspondances',
                    'description' => 'Suivre les besoins client détectés.',
                    'url' => route('scrapyard.correspondences.index'),
                    'icon' => 'matches',
                    'primary' => false,
                ],
            ];

            $partShortcuts = [
                ['label' => 'Toutes les pièces', 'value' => $stats['parts_total'] ?? 0, 'url' => route('scrapyard.parts.index')],
                ['label' => 'Pièces publiées', 'value' => $stats['publishedPartsCount'] ?? 0, 'url' => route('scrapyard.parts.index', ['publication' => 'published'])],
                ['label' => 'Pièces non publiées', 'value' => $stats['unpublishedPartsCount'] ?? 0, 'url' => route('scrapyard.parts.index', ['publication' => 'unpublished'])],
                ['label' => 'Pièces à préparer', 'value' => $stats['preparingPartsCount'] ?? 0, 'url' => route('scrapyard.parts.index', ['status' => 'preparing'])],
            ];

            $requestShortcuts = [
                ['label' => 'Toutes les demandes', 'value' => $stats['requests_total'] ?? 0, 'url' => route('scrapyard.requests.index')],
                ['label' => 'Demandes en attente', 'value' => $stats['pending_requests'] ?? 0, 'url' => route('scrapyard.requests.index', ['status' => 'pending'])],
                ['label' => 'Demandes acceptées', 'value' => $stats['accepted_requests'] ?? 0, 'url' => route('scrapyard.requests.index', ['status' => 'accepted'])],
                ['label' => 'Demandes refusées', 'value' => $stats['refused_requests'] ?? 0, 'url' => route('scrapyard.requests.index', ['status' => 'refused'])],
            ];

            $toTreatCards = [
                ['label' => 'Demandes en attente', 'value' => $stats['pending_requests'] ?? 0, 'url' => route('scrapyard.requests.index', ['status' => 'pending'])],
                ['label' => 'Pièces en préparation', 'value' => $stats['preparingPartsCount'] ?? 0, 'url' => route('scrapyard.parts.index', ['status' => 'preparing'])],
                ['label' => 'Pièces non publiées', 'value' => $stats['unpublishedPartsCount'] ?? 0, 'url' => route('scrapyard.parts.index', ['publication' => 'unpublished'])],
            ];
        @endphp

        <main class="min-h-screen w-full px-3 pb-8 pt-3 sm:px-6 sm:pb-10 sm:pt-4 md:pl-80 md:pr-6 md:pt-6 lg:pr-8">
            <div class="mx-auto w-full max-w-7xl">
                @include('scrapyard.partials.navigation', ['variant' => 'sidebar'])

                <header class="mt-4 flex flex-col gap-3 sm:gap-4 md:mt-0 xl:flex-row xl:items-center xl:justify-between">
                    <div class="max-w-3xl">
                        <x-ui.badge variant="orange" class="hidden md:inline-flex">Espace casse</x-ui.badge>
                        <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50 sm:mt-3 sm:text-3xl lg:text-4xl">
                            Tableau de bord casse
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm font-medium leading-5 text-zinc-600 dark:text-zinc-400 sm:mt-2 sm:text-base sm:leading-6">
                            Pilotez le stock, les demandes et les correspondances de {{ $scrapyardName }}@if ($scrapyardCity) à {{ $scrapyardCity }}@endif.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2 min-[375px]:grid-cols-2 sm:gap-3 xl:min-w-[34rem]">
                        <x-ui.card padding="p-3 sm:p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-zinc-400 sm:text-xs sm:tracking-[0.14em]">Compte connecté</p>
                            <p class="mt-1.5 truncate text-sm font-black text-zinc-950 dark:text-zinc-50 sm:mt-2">{{ $user?->name ?? 'Utilisateur casse' }}</p>
                            <p class="mt-1 truncate text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $scrapyardName }}</p>
                        </x-ui.card>

                        <x-ui.card padding="p-3 sm:p-4" class="flex items-center justify-between gap-2 sm:gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-zinc-400 sm:text-xs sm:tracking-[0.14em]">Notifications</p>
                                <p class="mt-1.5 text-sm font-black text-zinc-950 dark:text-zinc-50 sm:mt-2">
                                    {{ $unreadNotificationsCount }} non lue{{ $unreadNotificationsCount > 1 ? 's' : '' }}
                                </p>
                            </div>
                            <x-ui.button href="{{ route('notifications.index') }}" variant="secondary" size="sm" class="h-9 shrink-0 px-2.5 text-xs sm:h-10 sm:px-3 sm:text-sm">
                                Ouvrir
                            </x-ui.button>
                        </x-ui.card>
                    </div>
                </header>

                @if (! $scrapyard)
                    <x-ui.card class="mt-6 border-dashed border-orange-200 text-center" padding="p-6 sm:p-8">
                        <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Aucune casse n’est disponible.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            Le tableau de bord affichera les statistiques dès qu’une casse existera en base.
                        </p>
                    </x-ui.card>
                @else
                    <section class="mt-5 grid grid-cols-1 gap-3 min-[375px]:grid-cols-2 sm:mt-6 sm:gap-4 xl:grid-cols-4" aria-label="Statistiques principales">
                        @foreach ($primaryStats as $stat)
                            @php
                                $tone = $toneClasses[$stat['tone']] ?? $toneClasses['neutral'];
                            @endphp

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
                        @endforeach
                    </section>

                    <section class="mt-5 grid gap-4 sm:mt-6 sm:gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(340px,0.65fr)]">
                        <div class="space-y-4 sm:space-y-5">
                            <x-ui.card padding="p-4 sm:p-5">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <h2 class="text-xl font-black text-zinc-950 dark:text-zinc-50">Actions rapides</h2>
                                        <p class="mt-1 text-sm font-medium leading-5 text-zinc-600 dark:text-zinc-400 sm:leading-6">
                                            Les raccourcis opérationnels de la casse.
                                        </p>
                                    </div>
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
                                    <h2 class="text-lg font-black text-zinc-950 dark:text-zinc-50">Pilotage pièces</h2>
                                    <div class="mt-3 space-y-2 sm:mt-4">
                                        @foreach ($partShortcuts as $shortcut)
                                            <a href="{{ $shortcut['url'] }}" class="flex min-h-10 items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 transition hover:border-orange-200 hover:bg-white focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-800 dark:focus:ring-offset-zinc-950 dark:hover:bg-zinc-900 sm:min-h-12">
                                                <span class="text-xs font-black text-zinc-800 dark:text-zinc-200 sm:text-sm">{{ $shortcut['label'] }}</span>
                                                <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-[#C96504] ring-1 ring-orange-100 dark:bg-zinc-900 dark:ring-[#FC8505]/30">{{ $shortcut['value'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </x-ui.card>

                                <x-ui.card padding="p-3 sm:p-5">
                                    <h2 class="text-lg font-black text-zinc-950 dark:text-zinc-50">Pilotage demandes</h2>
                                    <div class="mt-3 space-y-2 sm:mt-4">
                                        @foreach ($requestShortcuts as $shortcut)
                                            <a href="{{ $shortcut['url'] }}" class="flex min-h-10 items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 transition hover:border-orange-200 hover:bg-white focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-800 dark:focus:ring-offset-zinc-950 dark:hover:bg-zinc-900 sm:min-h-12">
                                                <span class="text-xs font-black text-zinc-800 dark:text-zinc-200 sm:text-sm">{{ $shortcut['label'] }}</span>
                                                <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-[#C96504] ring-1 ring-orange-100 dark:bg-zinc-900 dark:ring-[#FC8505]/30">{{ $shortcut['value'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </x-ui.card>
                            </div>

                            <x-ui.card padding="p-0" class="overflow-hidden">
                                <div class="flex flex-col gap-2 border-b border-zinc-100 dark:border-zinc-800 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                                    <div>
                                        <h2 class="text-xl font-black text-zinc-950 dark:text-zinc-50">Dernières demandes</h2>
                                        <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">Les dernières demandes reçues par la casse.</p>
                                    </div>
                                    <x-ui.badge>5 dernières</x-ui.badge>
                                </div>

                                @if ($latestRequests->isEmpty())
                                    <div class="p-6 text-center sm:p-8">
                                        <h3 class="text-base font-black text-zinc-950 dark:text-zinc-50">Aucune demande reçue</h3>
                                        <p class="mt-1.5 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                            Les dernières demandes apparaîtront ici.
                                        </p>
                                    </div>
                                @else
                                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        @foreach ($latestRequests as $holdRequest)
                                            @php
                                                $part = $holdRequest->part;
                                                $vehicle = $part?->vehicle;
                                                $status = $holdRequest->status;
                                                $canShowClientContact = in_array($status, ['accepted', 'completed'], true);
                                                $createdAtDisplay = $holdRequest->created_at?->copy()->timezone($displayTimezone);
                                            @endphp

                                            <article class="p-4 sm:p-5">
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                    <div class="min-w-0">
                                                        <h3 class="text-base font-black text-zinc-950 dark:text-zinc-50">
                                                            {{ $part?->name ?? 'Pièce non renseignée' }}
                                                        </h3>
                                                        <p class="mt-1 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                                            {{ $vehicle?->brand ?? 'Marque inconnue' }} {{ $vehicle?->model ?? '' }}
                                                            @if ($vehicle?->year)
                                                                · {{ $vehicle->year }}
                                                            @endif
                                                        </p>
                                                        <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                                            {{ $canShowClientContact ? ($holdRequest->user?->name ?? 'Client non renseigné') : 'Demande client' }}
                                                        </p>
                                                    </div>

                                                    <x-ui.badge :variant="$statusVariants[$status] ?? 'neutral'">
                                                        {{ $statusLabels[$status] ?? $status }}
                                                    </x-ui.badge>
                                                </div>

                                                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                    <div>
                                                        <p class="text-xs font-medium text-zinc-400">
                                                            Reçue le {{ $createdAtDisplay?->format('d/m/Y à H:i') }}
                                                        </p>
                                                        @if ($part?->status)
                                                            <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400">
                                                                Pièce : {{ $partStatusLabels[$part->status] ?? $part->status }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <x-ui.button href="{{ route('scrapyard.requests.show', $holdRequest) }}" variant="secondary" size="sm" class="w-full sm:w-auto">
                                                        Voir la demande
                                                    </x-ui.button>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </x-ui.card>
                        </div>

                        <aside class="space-y-4 sm:space-y-5">
                            <x-ui.card padding="p-3 sm:p-5">
                                <div class="flex items-start gap-2.5 sm:gap-3">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#FC8505]/10 text-[#C96504] sm:h-10 sm:w-10 sm:rounded-2xl">
                                        <x-ui.icon name="clock" class="h-4 w-4 sm:h-5 sm:w-5" />
                                    </span>
                                    <div>
                                        <h2 class="text-lg font-black text-zinc-950 dark:text-zinc-50">À traiter</h2>
                                        <p class="mt-0.5 text-sm font-medium leading-5 text-zinc-600 dark:text-zinc-400 sm:mt-1 sm:leading-6">
                                            Les points qui demandent une action.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 space-y-2 sm:mt-4 sm:space-y-3">
                                    @foreach ($toTreatCards as $item)
                                        <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 transition hover:border-orange-200 hover:bg-white focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-800 dark:focus:ring-offset-zinc-950 dark:hover:bg-zinc-900 sm:rounded-2xl sm:p-4">
                                            <span class="text-sm font-black text-zinc-800 dark:text-zinc-200">{{ $item['label'] }}</span>
                                            <span class="text-xl font-black text-[#FC8505] sm:text-2xl">{{ $item['value'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </x-ui.card>

                            <x-ui.card padding="p-3 sm:p-5">
                                <h2 class="text-lg font-black text-zinc-950 dark:text-zinc-50">Vue d’ensemble</h2>
                                <p class="mt-0.5 text-sm font-medium leading-5 text-zinc-600 dark:text-zinc-400 sm:mt-1 sm:leading-6">
                                    Synthèse du stock et du traitement des demandes.
                                </p>

                                <div class="mt-3 space-y-3 sm:mt-4 sm:space-y-4">
                                    <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800 p-3 ring-1 ring-zinc-200 dark:ring-zinc-700 sm:rounded-2xl sm:p-4">
                                        <h3 class="text-sm font-black text-zinc-950 dark:text-zinc-50">Stock</h3>
                                        <div class="mt-2 space-y-1.5 sm:mt-3 sm:space-y-2">
                                            @foreach ($inventoryStats as $item)
                                                <div class="flex items-center justify-between gap-3 text-xs sm:text-sm">
                                                    <span class="font-semibold text-zinc-500 dark:text-zinc-400">{{ $item['label'] }}</span>
                                                    <span class="font-black text-zinc-950 dark:text-zinc-50">{{ $item['value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800 p-3 ring-1 ring-zinc-200 dark:ring-zinc-700 sm:rounded-2xl sm:p-4">
                                        <h3 class="text-sm font-black text-zinc-950 dark:text-zinc-50">Demandes</h3>
                                        <div class="mt-2 space-y-1.5 sm:mt-3 sm:space-y-2">
                                            @foreach ($requestStats as $item)
                                                <div class="flex items-center justify-between gap-3 text-xs sm:text-sm">
                                                    <span class="font-semibold text-zinc-500 dark:text-zinc-400">{{ $item['label'] }}</span>
                                                    <span class="font-black text-zinc-950 dark:text-zinc-50">{{ $item['value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </x-ui.card>
                        </aside>
                    </section>
                @endif
            </div>
        </main>
    </body>
</html>
