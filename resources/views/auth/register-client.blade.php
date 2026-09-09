<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Créer un compte client - Pièce Radar</title>

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
                <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50">Créer un compte client</h1>
                <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                    Suivez vos demandes de mise de côté en toute sécurité.
                </p>

                <form method="POST" action="{{ route('client.register.store') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Nom complet</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            required
                            autofocus
                            class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                        >
                        @error('name')
                            <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                        >
                        @error('email')
                            <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Téléphone</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            required
                            class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                        >
                        @error('phone')
                            <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Mot de passe</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                        >
                        @error('password')
                            <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Confirmation du mot de passe</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950"
                    >
                        Créer mon compte
                    </button>
                </form>

                <p class="mt-5 text-center text-sm font-medium text-zinc-600 dark:text-zinc-400">
                    Déjà un compte ?
                    <a href="{{ route('login') }}" class="font-black text-[#FC8505] hover:text-[#E87804]">Se connecter</a>
                </p>
            </section>
        </main>
    </body>
</html>
