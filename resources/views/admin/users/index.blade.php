<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Utilisateurs - Pièce Radar</title>

        <x-ui.theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        @php
            $roleLabels = [
                'client' => 'Client',
                'professional' => 'Garage / Mécanicien',
                'scrapyard' => 'Casse',
                'admin' => 'Administrateur',
            ];

            $roleVariants = [
                'client' => 'info',
                'professional' => 'success',
                'scrapyard' => 'orange',
                'admin' => 'dark',
            ];
        @endphp

        <main class="min-h-screen w-full px-3 pb-8 pt-3 sm:px-6 sm:pb-10 sm:pt-4 md:pl-80 md:pr-6 md:pt-6 lg:pr-8">
            <div class="mx-auto w-full max-w-7xl">
                @include('admin.partials.navigation')

                <header class="mt-4 flex flex-col gap-3 sm:gap-4 md:mt-0 xl:flex-row xl:items-center xl:justify-between">
                    <div class="max-w-3xl">
                        <x-ui.badge variant="orange" class="hidden md:inline-flex">Espace admin</x-ui.badge>
                        <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50 sm:mt-3 sm:text-3xl lg:text-4xl">
                            Utilisateurs
                        </h1>
                        <p class="mt-1.5 text-sm font-medium leading-5 text-zinc-600 dark:text-zinc-400 sm:mt-2 sm:text-base sm:leading-6">
                            {{ $users->count() }} compte{{ $users->count() > 1 ? 's' : '' }} enregistré{{ $users->count() > 1 ? 's' : '' }}.
                        </p>
                    </div>

                    <x-ui.button href="{{ route('admin.dashboard') }}" variant="secondary" size="md" class="w-full sm:w-auto">
                        Tableau de bord
                    </x-ui.button>
                </header>

                <section class="mt-5 space-y-3 sm:mt-6" aria-label="Liste des utilisateurs">
                    @foreach ($users as $user)
                        <x-ui.card as="article" padding="p-4 sm:p-5">
                            <div class="grid gap-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1.45fr)_minmax(13rem,0.7fr)_minmax(9rem,0.55fr)] lg:items-center">
                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Nom</p>
                                    <p class="mt-1.5 break-words text-base font-black text-zinc-950 dark:text-zinc-50">{{ $user->name }}</p>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Email</p>
                                    <p class="mt-1.5 break-words text-sm font-semibold leading-5 text-zinc-700 dark:text-zinc-300">{{ $user->email }}</p>
                                </div>

                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Rôle</p>
                                    <x-ui.badge :variant="$roleVariants[$user->role] ?? 'neutral'" class="mt-1.5">
                                        {{ $roleLabels[$user->role] ?? $user->role }}
                                    </x-ui.badge>
                                </div>

                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Créé le</p>
                                    <p class="mt-1.5 text-sm font-black text-zinc-700 dark:text-zinc-300">{{ $user->created_at?->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </x-ui.card>
                    @endforeach
                </section>
            </div>
        </main>
    </body>
</html>
