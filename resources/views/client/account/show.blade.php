<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mon compte - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-20 pt-5 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <a href="{{ route('client.parts.index') }}" class="inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                            Retour vers les pièces
                        </a>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('client.saved-searches.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                Mes recherches
                            </a>

                            <a href="{{ route('client.requests.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                Mes demandes
                            </a>

                            <a href="{{ route('notifications.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                Notifications
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-full bg-white px-3 py-1 text-xs font-black text-zinc-600 ring-1 ring-zinc-200 hover:text-zinc-900">
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />
                        <h1 class="mt-1 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                            Mon compte
                        </h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            Gérez vos informations client et votre mot de passe.
                        </p>
                    </div>
                </header>

                @if (session('success'))
                    <div class="mt-4 rounded-2xl border border-orange-200 bg-white p-4 text-sm font-bold text-[#C96504] shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-zinc-500">Nom</p>
                        <p class="mt-1 text-lg font-black text-zinc-950">{{ $user->name }}</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-zinc-500">Rôle</p>
                        <p class="mt-1 text-lg font-black text-zinc-950">Client</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-zinc-500">Email</p>
                        <p class="mt-1 break-words text-lg font-black text-zinc-950">{{ $user->email }}</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-zinc-500">Téléphone</p>
                        <p class="mt-1 text-lg font-black text-zinc-950">{{ $user->phone ?: 'Non renseigné' }}</p>
                    </div>
                </section>

                <section class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-zinc-950">Informations personnelles</h2>
                            <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                                L’adresse email est affichée pour information et ne peut pas être modifiée ici.
                            </p>
                        </div>

                        <a href="{{ route('client.requests.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-4 py-2 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                            Mes demandes
                        </a>
                    </div>

                    <a href="{{ route('client.saved-searches.index') }}" class="mt-3 inline-flex items-center text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                        Mes recherches enregistrées
                    </a>

                    <form method="POST" action="{{ route('client.account.update') }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="name" class="text-sm font-black text-zinc-900">Nom</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user->name) }}"
                                autocomplete="name"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                            @error('name')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="text-sm font-black text-zinc-900">Téléphone</label>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                value="{{ old('phone', $user->phone) }}"
                                autocomplete="tel"
                                class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                            @error('phone')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <p class="text-sm font-black text-zinc-900">Email</p>
                            <p class="mt-2 rounded-xl border border-zinc-200 bg-zinc-100 px-3 py-3 text-sm font-bold text-zinc-600">
                                {{ $user->email }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto"
                        >
                            Enregistrer les modifications
                        </button>
                    </form>
                </section>

                <section class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <h2 class="text-base font-black text-zinc-950">Modifier mon mot de passe</h2>
                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                        Votre mot de passe actuel est nécessaire pour valider ce changement.
                    </p>

                    <form method="POST" action="{{ route('client.account.password.update') }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="current_password" class="text-sm font-black text-zinc-900">Mot de passe actuel</label>
                            <div class="relative mt-2">
                                <input
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    autocomplete="current-password"
                                    class="h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 pr-12 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                >
                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center rounded-r-xl text-zinc-500 transition hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-inset"
                                    aria-label="Afficher le mot de passe"
                                    data-password-toggle
                                    data-password-target="current_password"
                                >
                                    <svg data-password-icon-show class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg data-password-icon-hide class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10.6 10.7a2 2 0 0 0 2.7 2.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M7.4 7.8C4.2 9.6 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.9-.4 4.1-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19.1 15.1A15 15 0 0 0 21.5 12S18 6 12 6c-.7 0-1.4.1-2 .2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="text-sm font-black text-zinc-900">Nouveau mot de passe</label>
                            <div class="relative mt-2">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="new-password"
                                    class="h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 pr-12 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                >
                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center rounded-r-xl text-zinc-500 transition hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-inset"
                                    aria-label="Afficher le mot de passe"
                                    data-password-toggle
                                    data-password-target="password"
                                >
                                    <svg data-password-icon-show class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg data-password-icon-hide class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10.6 10.7a2 2 0 0 0 2.7 2.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M7.4 7.8C4.2 9.6 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.9-.4 4.1-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19.1 15.1A15 15 0 0 0 21.5 12S18 6 12 6c-.7 0-1.4.1-2 .2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="text-sm font-black text-zinc-900">Confirmation du nouveau mot de passe</label>
                            <div class="relative mt-2">
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    class="h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 pr-12 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20"
                                >
                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center rounded-r-xl text-zinc-500 transition hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-inset"
                                    aria-label="Afficher le mot de passe"
                                    data-password-toggle
                                    data-password-target="password_confirmation"
                                >
                                    <svg data-password-icon-show class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg data-password-icon-hide class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10.6 10.7a2 2 0 0 0 2.7 2.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M7.4 7.8C4.2 9.6 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.9-.4 4.1-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19.1 15.1A15 15 0 0 0 21.5 12S18 6 12 6c-.7 0-1.4.1-2 .2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-4 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto"
                        >
                            Modifier mon mot de passe
                        </button>
                    </form>
                </section>
            </div>
        </main>

        <nav class="fixed inset-x-0 bottom-0 border-t border-zinc-200 bg-white/95 px-4 py-2 backdrop-blur sm:hidden">
            <div class="mx-auto grid max-w-md grid-cols-4 gap-2 text-center text-[11px] font-bold">
                <a href="{{ route('home') }}" class="text-zinc-500">
                    Accueil
                </a>
                <a href="{{ route('client.parts.index') }}" class="text-zinc-500">
                    Recherche
                </a>
                <a href="{{ route('client.requests.index') }}" class="text-zinc-500">
                    Demandes
                </a>
                <a href="{{ route('client.account.show') }}" class="text-[#FC8505]" aria-current="page">
                    Compte
                </a>
            </div>
        </nav>

        <script>
            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                const input = document.getElementById(button.dataset.passwordTarget);
                const showIcon = button.querySelector('[data-password-icon-show]');
                const hideIcon = button.querySelector('[data-password-icon-hide]');

                if (! input) {
                    return;
                }

                const syncState = () => {
                    const isVisible = input.type === 'text';

                    button.setAttribute('aria-label', isVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                    showIcon?.classList.toggle('hidden', isVisible);
                    hideIcon?.classList.toggle('hidden', ! isVisible);
                };

                button.addEventListener('click', () => {
                    input.type = input.type === 'password' ? 'text' : 'password';
                    syncState();
                });

                syncState();
            });
        </script>
    </body>
</html>
