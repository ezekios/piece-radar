@props([
    'title' => 'Pièce Radar',
    'cardWidth' => 'max-w-md',
    'showLogo' => true,
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
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="mx-auto w-full {{ $cardWidth }} rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    @if ($showLogo)
                        <x-brand-logo :href="route('home')" image-class="h-12 w-auto max-w-[180px] object-contain" theme-aware />
                    @endif

                    <x-ui.theme-toggle />
                </div>

                @isset($heading)
                    <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50">{{ $heading }}</h1>
                @endisset

                @isset($description)
                    <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">{{ $description }}</p>
                @endisset

                {{ $slot }}
            </section>
        </main>
    </body>
</html>
