<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mon compte professionnel - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $inputClass = 'mt-2 h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20';
            $labelClass = 'text-sm font-black text-zinc-900';
            $navLinks = [
                ['label' => 'Recherche', 'url' => route('client.parts.index')],
                ['label' => 'Mes recherches', 'url' => route('client.saved-searches.index')],
                ['label' => 'Mes demandes', 'url' => route('client.requests.index')],
                ['label' => 'Notifications', 'url' => route('notifications.index')],
            ];

            $businessDetails = [
                ['label' => 'Entreprise', 'value' => $profile?->company_name ?: 'Non renseignée'],
                ['label' => 'SIRET', 'value' => $profile?->siret ?: 'Non renseigné'],
                ['label' => 'Adresse', 'value' => $profile?->address ?: 'Non renseignée'],
                ['label' => 'Code postal', 'value' => $profile?->postal_code ?: 'Non renseigné'],
                ['label' => 'Ville', 'value' => $profile?->city ?: 'Non renseignée'],
            ];

            $accountDetails = [
                ['label' => 'Responsable', 'value' => $user->name],
                ['label' => 'Email de connexion', 'value' => $user->email],
                ['label' => 'Téléphone', 'value' => $user->phone ?: 'Non renseigné'],
                ['label' => 'Rôle', 'value' => 'Garage / Mécanicien'],
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-6xl px-3 pb-24 pt-4 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-5xl">
                <header class="border-b border-zinc-200/80 pb-5">
                    <div class="flex items-center justify-between gap-3">
                        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />

                        <div class="hidden flex-wrap items-center gap-2 sm:flex">
                            @foreach ($navLinks as $link)
                                <a href="{{ $link['url'] }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                    {{ $link['label'] }}
                                </a>
                            @endforeach

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.36fr)] lg:items-end">
                        <div>
                            <x-ui.badge variant="orange">Espace professionnel</x-ui.badge>
                            <h1 class="mt-3 text-2xl font-black leading-tight text-zinc-950 sm:text-3xl lg:text-4xl">
                                Mon compte garage / mécanicien
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-zinc-600 sm:text-base">
                                Gérez votre profil professionnel, vos coordonnées et la sécurité du compte.
                            </p>
                        </div>

                        <x-ui.card padding="p-4" class="hidden lg:block">
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-zinc-400">Compte connecté</p>
                            <p class="mt-2 truncate text-sm font-black text-zinc-950">{{ $user->name }}</p>
                            <p class="mt-1 truncate text-xs font-semibold text-zinc-500">{{ $user->email }}</p>
                        </x-ui.card>
                    </div>
                </header>

                @if (session('success'))
                    <x-ui.card class="mt-5 border-orange-200 text-sm font-bold text-[#C96504]" padding="p-4">
                        {{ session('success') }}
                    </x-ui.card>
                @endif

                <section class="mt-5 grid gap-4 lg:grid-cols-2" aria-label="Résumé du compte professionnel">
                    <x-ui.card as="article" padding="p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#FC8505]/10 text-[#C96504]">
                                <x-ui.icon name="building" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-lg font-black text-zinc-950">Entreprise</h2>
                                <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                                    Informations du garage, atelier ou professionnel acheteur.
                                </p>
                            </div>
                        </div>

                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($businessDetails as $detail)
                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">{{ $detail['label'] }}</dt>
                                    <dd class="mt-1.5 break-words text-sm font-black text-zinc-950">{{ $detail['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-ui.card>

                    <x-ui.card as="article" padding="p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">
                                <x-ui.icon name="account" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-lg font-black text-zinc-950">Responsable du compte</h2>
                                <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                                    Coordonnées utilisées pour vos recherches et demandes.
                                </p>
                            </div>
                        </div>

                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($accountDetails as $detail)
                                <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                                    <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">{{ $detail['label'] }}</dt>
                                    <dd class="mt-1.5 break-words text-sm font-black text-zinc-950">{{ $detail['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-ui.card>
                </section>

                <section class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                    <x-ui.card as="section" padding="p-4 sm:p-5">
                        <h2 class="text-lg font-black text-zinc-950">Compte utilisateur</h2>
                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                            L’adresse email est affichée pour information et ne peut pas être modifiée ici.
                        </p>

                        <form method="POST" action="{{ route('professional.account.update') }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="name" class="{{ $labelClass }}">Nom du responsable</label>
                                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" class="{{ $inputClass }}">
                                @error('name')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="{{ $labelClass }}">Téléphone</label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" class="{{ $inputClass }}">
                                @error('phone')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full sm:w-auto">
                                Enregistrer le compte
                            </x-ui.button>
                        </form>
                    </x-ui.card>

                    <x-ui.card as="section" padding="p-4 sm:p-5" class="h-fit">
                        <h2 class="text-lg font-black text-zinc-950">Informations professionnelles</h2>
                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                            Ces informations décrivent le garage, l’atelier ou l’entreprise qui recherche des pièces.
                        </p>

                        <form method="POST" action="{{ route('professional.account.profile.update') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                            @csrf
                            @method('PATCH')

                            <div class="sm:col-span-2">
                                <label for="company_name" class="{{ $labelClass }}">Nom de l’entreprise / garage</label>
                                <input id="company_name" name="company_name" type="text" value="{{ old('company_name', $profile?->company_name) }}" class="{{ $inputClass }}">
                                @error('company_name')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="siret" class="{{ $labelClass }}">SIRET</label>
                                <input id="siret" name="siret" type="text" value="{{ old('siret', $profile?->siret) }}" class="{{ $inputClass }}">
                                @error('siret')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="city" class="{{ $labelClass }}">Ville</label>
                                <input id="city" name="city" type="text" value="{{ old('city', $profile?->city) }}" class="{{ $inputClass }}">
                                @error('city')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="postal_code" class="{{ $labelClass }}">Code postal</label>
                                <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $profile?->postal_code) }}" class="{{ $inputClass }}">
                                @error('postal_code')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="address" class="{{ $labelClass }}">Adresse</label>
                                <input id="address" name="address" type="text" value="{{ old('address', $profile?->address) }}" class="{{ $inputClass }}">
                                @error('address')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full sm:w-auto">
                                    Enregistrer les informations professionnelles
                                </x-ui.button>
                            </div>
                        </form>
                    </x-ui.card>

                    <x-ui.card as="section" padding="p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                                <x-ui.icon name="shield" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-lg font-black text-zinc-950">Modifier mon mot de passe</h2>
                                <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                                    Utilisez votre mot de passe actuel pour valider le changement.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('professional.account.password.update') }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="current_password" class="{{ $labelClass }}">Mot de passe actuel</label>
                                <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="{{ $inputClass }}">
                                @error('current_password')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="{{ $labelClass }}">Nouveau mot de passe</label>
                                <input id="password" name="password" type="password" autocomplete="new-password" class="{{ $inputClass }}">
                                @error('password')
                                    <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="{{ $labelClass }}">Confirmation du nouveau mot de passe</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="{{ $inputClass }}">
                            </div>

                            <x-ui.button as="button" type="submit" variant="secondary" size="lg" class="w-full sm:w-auto">
                                Mettre à jour le mot de passe
                            </x-ui.button>
                        </form>
                    </x-ui.card>
                </section>
            </div>
        </main>

        <x-client.mobile-navigation active="account" />
    </body>
</html>
