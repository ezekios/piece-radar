<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Compte en attente - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="mx-auto w-full max-w-xl rounded-2xl border border-zinc-200 bg-white p-5 text-center shadow-sm sm:p-6">
                <x-brand-logo :href="route('home')" image-class="mx-auto h-12 w-auto max-w-[180px] object-contain" />

                <span class="mt-5 inline-flex rounded-full bg-[#FC8505]/10 px-3 py-1 text-xs font-black text-[#C96504]">
                    Validation en attente
                </span>

                <h1 class="mt-3 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                    Votre compte professionnel est en attente de validation.
                </h1>

                <p class="mt-3 text-sm font-medium leading-6 text-zinc-600">
                    Votre inscription a bien été enregistrée. Un administrateur doit valider votre casse avant l’accès complet à Pièce Radar.
                </p>

                @if ($scrapyard)
                    <div class="mt-5 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-left">
                        <p class="text-xs font-black uppercase text-zinc-500">Casse enregistrée</p>
                        <p class="mt-1 text-lg font-black text-zinc-950">{{ $scrapyard->name }}</p>
                        <p class="mt-1 text-sm font-medium text-zinc-600">{{ $scrapyard->city ?: 'Ville non renseignée' }}</p>
                        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                            <div class="rounded-xl bg-white p-3 ring-1 ring-zinc-200">
                                <p class="text-xs font-bold text-zinc-500">SIRET déclaré</p>
                                <p class="mt-1 font-black text-zinc-950">{{ $scrapyard->siret ?: 'Non renseigné' }}</p>
                            </div>
                            <div class="rounded-xl bg-white p-3 ring-1 ring-zinc-200">
                                <p class="text-xs font-bold text-zinc-500">Statut email</p>
                                <p class="mt-1 font-black text-emerald-700">Email vérifié</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="mt-5">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-5 py-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto"
                    >
                        Se déconnecter
                    </button>
                </form>
            </section>
        </main>
    </body>
</html>
