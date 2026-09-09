@props([
    'title' => 'Pièce Radar',
    'active' => 'search',
    'maxWidth' => 'max-w-5xl',
    'contentWidth' => 'max-w-3xl',
])

@php
    $user = auth()->user();
    $isProfessional = $user?->role === 'professional';
    $isBuyer = in_array($user?->role, ['client', 'professional'], true);
    $accountRoute = $isProfessional ? route('professional.account.show') : route('client.account.show');
@endphp

@if ($isProfessional)
    <x-layouts.professional :title="$title" :active="$active" :max-width="$maxWidth">
        @isset($header)
            <x-slot:header>
                {{ $header }}
            </x-slot:header>
        @endisset

        {{ $slot }}
    </x-layouts.professional>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <title>{{ $title }}</title>

            <x-ui.theme-script />
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        </head>
        <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
            <main class="mx-auto min-h-screen w-full {{ $maxWidth }} px-4 pb-24 pt-5 sm:px-6 sm:pb-10 lg:px-8">
                <div class="mx-auto w-full {{ $contentWidth }}">
                    <header class="border-b border-zinc-200/80 pb-4 dark:border-zinc-800">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" theme-aware />

                            <div class="flex items-center gap-2">
                                <x-ui.theme-toggle class="sm:hidden" />
                            </div>

                            <nav class="hidden flex-wrap items-center gap-2 sm:flex" aria-label="Navigation acheteur">
                                <x-ui.theme-toggle />

                                <a href="{{ route('client.parts.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:focus:ring-offset-zinc-950 dark:hover:border-[#FC8505]/40 dark:hover:text-[#FC8505]">
                                    Recherche
                                </a>

                                @auth
                                    @if ($isBuyer)
                                        <a href="{{ route('client.saved-searches.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:focus:ring-offset-zinc-950 dark:hover:border-[#FC8505]/40 dark:hover:text-[#FC8505]">
                                            Mes recherches
                                        </a>

                                        <a href="{{ route('client.requests.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:focus:ring-offset-zinc-950 dark:hover:border-[#FC8505]/40 dark:hover:text-[#FC8505]">
                                            Mes demandes
                                        </a>

                                        <a href="{{ route('notifications.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:focus:ring-offset-zinc-950 dark:hover:border-[#FC8505]/40 dark:hover:text-[#FC8505]">
                                            Notifications
                                        </a>

                                        <a href="{{ $accountRoute }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-[#FC8505] px-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950">
                                            Mon compte
                                        </a>

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="inline-flex h-10 cursor-pointer items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:focus:ring-offset-zinc-950 dark:hover:border-[#FC8505]/40 dark:hover:text-[#FC8505]">
                                                Déconnexion
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-[#FC8505] px-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950">
                                        Connexion
                                    </a>
                                @endauth
                            </nav>
                        </div>

                        @isset($header)
                            <div class="mt-4">
                                {{ $header }}
                            </div>
                        @endisset
                    </header>

                    {{ $slot }}
                </div>
            </main>

            <x-client.mobile-navigation :active="$active" />
        </body>
    </html>
@endif
