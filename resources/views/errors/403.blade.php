<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Accès non autorisé - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="mx-auto w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-5 text-center shadow-sm sm:p-6">
                <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                <p class="mt-4 text-xs font-black uppercase text-zinc-500">Erreur 403</p>
                <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950">Accès non autorisé</h1>
                <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">
                    Vous n'avez pas l'autorisation d'accéder à cette page.
                </p>
                <a href="{{ route('home') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                    Retour à l'accueil
                </a>
            </section>
        </main>
    </body>
</html>
