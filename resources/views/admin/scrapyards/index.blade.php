<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Casses - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="min-h-screen w-full px-3 pb-8 pt-3 sm:px-6 sm:pb-10 sm:pt-4 md:pl-80 md:pr-6 md:pt-6 lg:pr-8">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mb-3 flex items-center justify-between gap-3 md:hidden">
                    <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />
                    <x-ui.badge variant="orange">Administrateur</x-ui.badge>
                </div>

                @include('admin.partials.navigation')

                <header class="mt-4 flex flex-col gap-3 sm:gap-4 md:mt-0 xl:flex-row xl:items-center xl:justify-between">
                    <div class="max-w-3xl">
                        <x-ui.badge variant="orange" class="hidden md:inline-flex">Espace admin</x-ui.badge>
                        <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 sm:mt-3 sm:text-3xl lg:text-4xl">
                            Casses
                        </h1>
                        <p class="mt-1.5 text-sm font-medium leading-5 text-zinc-600 sm:mt-2 sm:text-base sm:leading-6">
                            {{ $scrapyards->count() }} casse{{ $scrapyards->count() > 1 ? 's' : '' }} référencée{{ $scrapyards->count() > 1 ? 's' : '' }}.
                        </p>
                    </div>

                    <x-ui.button href="{{ route('admin.dashboard') }}" variant="secondary" size="md" class="w-full sm:w-auto">
                        Tableau de bord
                    </x-ui.button>
                </header>

                @if (session('success'))
                    <x-ui.card class="mt-5 border-orange-200 text-sm font-bold text-[#C96504]" padding="p-4">
                        {{ session('success') }}
                    </x-ui.card>
                @endif

                @if (session('error'))
                    <x-ui.card class="mt-5 border-red-200 text-sm font-bold text-red-700" padding="p-4">
                        {{ session('error') }}
                    </x-ui.card>
                @endif

                <section class="mt-5 grid gap-3 sm:mt-6" aria-label="Liste des casses">
                    @foreach ($scrapyards as $scrapyard)
                        @php
                            $emailVerified = (bool) $scrapyard->user?->hasVerifiedEmail();
                        @endphp

                        <x-ui.card as="article" padding="p-4 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="break-words text-lg font-black text-zinc-950">{{ $scrapyard->name }}</h2>
                                        <x-ui.badge :variant="$scrapyard->is_active ? 'success' : 'orange'">
                                            {{ $scrapyard->is_active ? 'Active' : 'En attente' }}
                                        </x-ui.badge>
                                    </div>

                                    <p class="mt-1 text-sm font-semibold text-zinc-600">{{ $scrapyard->city ?: 'Ville non renseignée' }}</p>
                                </div>

                                <form method="POST" action="{{ route('admin.scrapyards.update-status', $scrapyard) }}" class="w-full sm:w-auto">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $scrapyard->is_active ? '0' : '1' }}">
                                    <x-ui.button as="button" type="submit" :variant="$scrapyard->is_active ? 'secondary' : 'primary'" size="md" class="w-full sm:w-auto">
                                        {{ $scrapyard->is_active ? 'Désactiver' : 'Activer' }}
                                    </x-ui.button>
                                </form>
                            </div>

                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 xl:grid-cols-3">
                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Email</dt>
                                    <dd class="mt-1.5 break-words font-black text-zinc-950">{{ $scrapyard->email ?: $scrapyard->user?->email ?: 'Non renseigné' }}</dd>
                                </div>

                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Statut email</dt>
                                    <dd class="mt-1.5">
                                        <x-ui.badge :variant="$emailVerified ? 'success' : 'orange'">
                                            {{ $emailVerified ? 'Vérifié' : 'Non vérifié' }}
                                        </x-ui.badge>
                                    </dd>
                                </div>

                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">SIRET déclaré</dt>
                                    <dd class="mt-1.5 break-words font-black text-zinc-950">{{ $scrapyard->siret ?: 'Non renseigné' }}</dd>
                                </div>

                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Téléphone</dt>
                                    <dd class="mt-1.5 font-black text-zinc-950">{{ $scrapyard->phone ?: 'Non renseigné' }}</dd>
                                </div>

                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Responsable</dt>
                                    <dd class="mt-1.5 break-words font-black text-zinc-950">{{ $scrapyard->user?->name ?: 'Non renseigné' }}</dd>
                                </div>

                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Statut d’activation</dt>
                                    <dd class="mt-1.5">
                                        <x-ui.badge :variant="$scrapyard->is_active ? 'success' : 'orange'">
                                            {{ $scrapyard->is_active ? 'Active' : 'En attente' }}
                                        </x-ui.badge>
                                    </dd>
                                </div>
                            </dl>
                        </x-ui.card>
                    @endforeach
                </section>
            </div>
        </main>
    </body>
</html>
