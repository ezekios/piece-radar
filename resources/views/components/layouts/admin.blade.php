@props([
    'title' => 'Administration - Pièce Radar',
    'maxWidth' => 'max-w-7xl',
    'badge' => 'Administrateur',
])

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
        <main class="min-h-screen w-full px-3 pb-8 pt-3 sm:px-6 sm:pb-10 sm:pt-4 md:pl-80 md:pr-6 md:pt-6 lg:pr-8">
            <div class="mx-auto w-full {{ $maxWidth }}">
                @include('admin.partials.navigation')

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
