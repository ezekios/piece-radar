<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Inscription casse - Pièce Radar</title>

        <x-ui.theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="mx-auto w-full max-w-3xl rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <x-brand-logo :href="route('home')" image-class="h-12 w-auto max-w-[180px] object-contain" theme-aware />
                    <x-ui.theme-toggle />
                </div>
                <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50">Créer un compte casse</h1>
                <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
                    Votre inscription sera examinée par un administrateur avant l’ouverture complète de votre espace.
                </p>

                <form method="POST" action="{{ route('scrapyard.register.store') }}" class="mt-5 space-y-5">
                    @csrf

                    <section class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Compte de connexion</h2>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="name" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Nom du responsable</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name') }}"
                                    autocomplete="name"
                                    required
                                    autofocus
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                @error('name')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Email de connexion</label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    autocomplete="email"
                                    required
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                @error('email')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Téléphone du responsable</label>
                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone') }}"
                                    autocomplete="tel"
                                    required
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
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
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
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
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <h2 class="text-base font-black text-zinc-950 dark:text-zinc-50">Informations de la casse</h2>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="scrapyard_name" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Nom de la casse</label>
                                <input
                                    id="scrapyard_name"
                                    name="scrapyard_name"
                                    type="text"
                                    value="{{ old('scrapyard_name') }}"
                                    required
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                @error('scrapyard_name')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="siret" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">SIRET</label>
                                <input
                                    id="siret"
                                    name="siret"
                                    type="text"
                                    value="{{ old('siret') }}"
                                    inputmode="numeric"
                                    pattern="[0-9]{14}"
                                    maxlength="14"
                                    required
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                <p class="mt-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">SIRET déclaré, 14 chiffres.</p>
                                @error('siret')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="scrapyard_email" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Email professionnel</label>
                                <input
                                    id="scrapyard_email"
                                    name="scrapyard_email"
                                    type="email"
                                    value="{{ old('scrapyard_email') }}"
                                    autocomplete="email"
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                @error('scrapyard_email')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="scrapyard_phone" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Téléphone professionnel</label>
                                <input
                                    id="scrapyard_phone"
                                    name="scrapyard_phone"
                                    type="tel"
                                    value="{{ old('scrapyard_phone') }}"
                                    autocomplete="tel"
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                @error('scrapyard_phone')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="postal_code" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Code postal</label>
                                <input
                                    id="postal_code"
                                    name="postal_code"
                                    type="text"
                                    value="{{ old('postal_code') }}"
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                @error('postal_code')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="city" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Ville</label>
                                <input
                                    id="city"
                                    name="city"
                                    type="text"
                                    value="{{ old('city') }}"
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                @error('city')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="address" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Adresse</label>
                                <input
                                    id="address"
                                    name="address"
                                    type="text"
                                    value="{{ old('address') }}"
                                    autocomplete="street-address"
                                    class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                                >
                                @error('address')
                                    <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="description" class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Description</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950"
                    >
                        Envoyer l’inscription
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
