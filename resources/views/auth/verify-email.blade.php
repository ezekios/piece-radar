<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Vérification email - Pièce Radar</title>

        <x-ui.theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="mx-auto w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <x-brand-logo :href="route('home')" image-class="h-12 w-auto max-w-[180px] object-contain" theme-aware />
                    <x-ui.theme-toggle />
                </div>
                <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50">Vérifiez votre email</h1>
                <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                    Un lien de vérification a été envoyé à votre adresse email.
                </p>

                <div class="mt-4 rounded-2xl border border-orange-100 bg-[#FC8505]/5 p-4">
                    <p class="text-xs font-bold text-[#C96504]">Adresse du compte</p>
                    <p class="mt-1 break-words text-sm font-black text-zinc-950 dark:text-zinc-50">{{ auth()->user()?->email }}</p>
                </div>

                @if (session('status'))
                    <div class="mt-4 rounded-2xl border border-orange-200 bg-white p-4 text-sm font-bold text-[#C96504] shadow-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mt-5 space-y-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950"
                        >
                            Renvoyer le lien
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-5 py-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 dark:focus:ring-offset-zinc-950 dark:hover:border-[#FC8505]/40 dark:hover:text-[#FC8505]"
                        >
                            Déconnexion
                        </button>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
