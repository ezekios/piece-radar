<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mon compte professionnel - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-20 pt-5 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('client.parts.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                Recherche
                            </a>

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
                        <span class="inline-flex rounded-full bg-[#FC8505]/10 px-3 py-1 text-xs font-black text-[#C96504] ring-1 ring-[#FC8505]/20">
                            Espace professionnel
                        </span>
                        <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">
                            Mon compte garage / mécanicien
                        </h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            Gérez vos informations de contact et les informations de votre entreprise.
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
                        <p class="text-xs font-black uppercase text-zinc-500">Responsable</p>
                        <p class="mt-1 text-lg font-black text-zinc-950">{{ $user->name }}</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-zinc-500">Rôle</p>
                        <p class="mt-1 text-lg font-black text-zinc-950">Garage / Mécanicien</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-zinc-500">Email de connexion</p>
                        <p class="mt-1 break-words text-lg font-black text-zinc-950">{{ $user->email }}</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase text-zinc-500">Téléphone</p>
                        <p class="mt-1 text-lg font-black text-zinc-950">{{ $user->phone ?: 'Non renseigné' }}</p>
                    </div>
                </section>

                <section class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <h2 class="text-base font-black text-zinc-950">Compte utilisateur</h2>
                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                        L’adresse email est affichée pour information et ne peut pas être modifiée ici.
                    </p>

                    <form method="POST" action="{{ route('professional.account.update') }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="name" class="text-sm font-black text-zinc-900">Nom du responsable</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('name')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="text-sm font-black text-zinc-900">Téléphone</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('phone')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                            Enregistrer le compte
                        </button>
                    </form>
                </section>

                <section class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <h2 class="text-base font-black text-zinc-950">Informations professionnelles</h2>
                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                        Ces informations décrivent le garage, l’atelier ou l’entreprise qui recherche des pièces.
                    </p>

                    <form method="POST" action="{{ route('professional.account.profile.update') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                        @csrf
                        @method('PATCH')

                        <div class="sm:col-span-2">
                            <label for="company_name" class="text-sm font-black text-zinc-900">Nom de l’entreprise / garage</label>
                            <input id="company_name" name="company_name" type="text" value="{{ old('company_name', $profile?->company_name) }}" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('company_name')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="siret" class="text-sm font-black text-zinc-900">SIRET</label>
                            <input id="siret" name="siret" type="text" value="{{ old('siret', $profile?->siret) }}" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('siret')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="text-sm font-black text-zinc-900">Ville</label>
                            <input id="city" name="city" type="text" value="{{ old('city', $profile?->city) }}" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('city')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="postal_code" class="text-sm font-black text-zinc-900">Code postal</label>
                            <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $profile?->postal_code) }}" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('postal_code')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address" class="text-sm font-black text-zinc-900">Adresse</label>
                            <input id="address" name="address" type="text" value="{{ old('address', $profile?->address) }}" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('address')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                Enregistrer les informations professionnelles
                            </button>
                        </div>
                    </form>
                </section>

                <section class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <h2 class="text-base font-black text-zinc-950">Modifier mon mot de passe</h2>

                    <form method="POST" action="{{ route('professional.account.password.update') }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="current_password" class="text-sm font-black text-zinc-900">Mot de passe actuel</label>
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('current_password')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="text-sm font-black text-zinc-900">Nouveau mot de passe</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                            @error('password')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="text-sm font-black text-zinc-900">Confirmation du nouveau mot de passe</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20">
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-5 py-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                            Mettre à jour le mot de passe
                        </button>
                    </form>
                </section>
            </div>
        </main>

        <nav class="fixed inset-x-0 bottom-0 border-t border-zinc-200 bg-white/95 px-4 py-2 backdrop-blur sm:hidden">
            <div class="mx-auto grid max-w-md grid-cols-4 gap-2 text-center text-[11px] font-bold">
                <a href="{{ route('home') }}" class="text-zinc-500">Accueil</a>
                <a href="{{ route('client.parts.index') }}" class="text-zinc-500">Recherche</a>
                <a href="{{ route('client.requests.index') }}" class="text-zinc-500">Demandes</a>
                <a href="{{ route('professional.account.show') }}" class="text-[#FC8505]" aria-current="page">Compte</a>
            </div>
        </nav>
    </body>
</html>
