@props([
    'title' => 'Espace professionnel - Pièce Radar',
    'active' => null,
    'maxWidth' => 'max-w-7xl',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="min-h-screen w-full px-3 pb-24 pt-3 sm:px-6 sm:pt-4 md:pl-80 md:pr-6 md:pb-10 md:pt-6 lg:pr-8">
            <div class="mx-auto w-full {{ $maxWidth }}">
                <div class="mb-3 flex items-center justify-between gap-3 md:hidden">
                    <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />
                    <x-ui.badge variant="orange">Espace pro</x-ui.badge>
                </div>

                <x-professional.navigation :active="$active" />

                @isset($header)
                    <header class="mt-4 md:mt-0">
                        {{ $header }}
                    </header>
                @endisset

                {{ $slot }}
            </div>
        </main>
    </body>
</html>
