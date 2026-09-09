@php
    $passwordInputClass = 'h-12 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 pr-12 text-sm font-medium text-zinc-900 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20';

    $scrapyardDetails = [
        ['label' => 'Casse', 'value' => $scrapyard->name],
        ['label' => 'Email professionnel', 'value' => $scrapyard->email ?: 'Non renseigné'],
        ['label' => 'Téléphone professionnel', 'value' => $scrapyard->phone ?: 'Non renseigné'],
        ['label' => 'Adresse', 'value' => $scrapyard->address ?: 'Non renseignée'],
        ['label' => 'Code postal', 'value' => $scrapyard->postal_code ?: 'Non renseigné'],
        ['label' => 'Ville', 'value' => $scrapyard->city ?: 'Non renseignée'],
    ];

    $accountDetails = [
        ['label' => 'Responsable', 'value' => $user->name],
        ['label' => 'Email de connexion', 'value' => $user->email],
        ['label' => 'Téléphone', 'value' => $user->phone ?: 'Non renseigné'],
        ['label' => 'Rôle', 'value' => 'Professionnel / Casse'],
    ];
@endphp

<x-layouts.scrapyard title="Mon compte casse - Pièce Radar" max-width="max-w-6xl">
    <x-slot:header>
        <x-ui.page-header
            eyebrow="Espace casse"
            title="Mon compte casse"
            description="Gérez votre compte de connexion, les informations publiques de votre casse et la sécurité du compte."
        >
            <x-slot:actions>
                <x-ui.badge :variant="$scrapyard->is_active ? 'success' : 'neutral'">
                    {{ $scrapyard->is_active ? 'Casse active' : 'Casse inactive' }}
                </x-ui.badge>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot:header>

    @if (session('success'))
        <x-ui.alert class="mt-5" variant="success">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <section class="mt-5 grid gap-4 lg:grid-cols-2" aria-label="Résumé du compte casse">
        <x-ui.card as="article" padding="p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#FC8505]/10 text-[#C96504]">
                    <x-ui.icon name="building" class="h-5 w-5" />
                </span>

                <div class="min-w-0">
                    <h2 class="text-lg font-black text-zinc-950">Casse</h2>
                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                        Informations publiques de votre établissement.
                    </p>
                </div>
            </div>

            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($scrapyardDetails as $detail)
                    <div class="rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                        <dt class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">{{ $detail['label'] }}</dt>
                        <dd class="mt-1.5 break-words text-sm font-black text-zinc-950">{{ $detail['value'] }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($scrapyard->description)
                <div class="mt-3 rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200">
                    <p class="text-xs font-black uppercase tracking-[0.12em] text-zinc-400">Description</p>
                    <p class="mt-1.5 break-words text-sm font-semibold leading-6 text-zinc-700">{{ $scrapyard->description }}</p>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card as="article" padding="p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">
                    <x-ui.icon name="account" class="h-5 w-5" />
                </span>

                <div class="min-w-0">
                    <h2 class="text-lg font-black text-zinc-950">Responsable du compte</h2>
                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                        Coordonnées utilisées pour la gestion de l’espace casse.
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

    <section class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <x-ui.card as="section" padding="p-4 sm:p-5">
            <h2 class="text-lg font-black text-zinc-950">Mon compte professionnel</h2>
            <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                L’email de connexion est affiché pour information et ne peut pas être modifié ici.
            </p>

            <form method="POST" action="{{ route('scrapyard.account.update') }}" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')

                <x-ui.input
                    id="account_name"
                    name="name"
                    type="text"
                    label="Nom du compte"
                    value="{{ $user->name }}"
                    autocomplete="name"
                />

                <x-ui.input
                    id="account_phone"
                    name="phone"
                    type="tel"
                    label="Téléphone"
                    value="{{ $user->phone }}"
                    autocomplete="tel"
                />

                <div>
                    <p class="text-sm font-black text-zinc-900">Email de connexion</p>
                    <p class="mt-2 break-words rounded-xl border border-zinc-200 bg-zinc-100 px-3 py-3 text-sm font-bold text-zinc-600">
                        {{ $user->email }}
                    </p>
                </div>

                <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full min-[390px]:w-auto">
                    Enregistrer le compte
                </x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card as="section" padding="p-4 sm:p-5" class="h-fit">
            <div class="flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-start min-[390px]:justify-between">
                <div>
                    <h2 class="text-lg font-black text-zinc-950">Informations de la casse</h2>
                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                        Ces informations décrivent votre établissement professionnel.
                    </p>
                </div>

                <x-ui.badge :variant="$scrapyard->is_active ? 'success' : 'neutral'">
                    {{ $scrapyard->is_active ? 'Active' : 'Inactive' }}
                </x-ui.badge>
            </div>

            <form method="POST" action="{{ route('scrapyard.account.scrapyard.update') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PATCH')

                <x-ui.input
                    id="scrapyard_name"
                    name="name"
                    type="text"
                    label="Nom de la casse"
                    value="{{ $scrapyard->name }}"
                />

                <x-ui.input
                    id="scrapyard_email"
                    name="email"
                    type="email"
                    label="Email professionnel"
                    value="{{ $scrapyard->email }}"
                    autocomplete="email"
                />

                <x-ui.input
                    id="scrapyard_phone"
                    name="phone"
                    type="tel"
                    label="Téléphone professionnel"
                    value="{{ $scrapyard->phone }}"
                    autocomplete="tel"
                />

                <x-ui.input
                    id="scrapyard_postal_code"
                    name="postal_code"
                    type="text"
                    label="Code postal"
                    value="{{ $scrapyard->postal_code }}"
                />

                <x-ui.input
                    id="scrapyard_city"
                    name="city"
                    type="text"
                    label="Ville"
                    value="{{ $scrapyard->city }}"
                />

                <x-ui.input
                    id="scrapyard_address"
                    name="address"
                    type="text"
                    label="Adresse"
                    value="{{ $scrapyard->address }}"
                    autocomplete="street-address"
                />

                <div class="sm:col-span-2">
                    <x-ui.textarea
                        id="scrapyard_description"
                        name="description"
                        label="Description"
                        rows="5"
                        value="{{ $scrapyard->description }}"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-ui.button as="button" type="submit" variant="primary" size="lg" class="w-full min-[390px]:w-auto">
                        Enregistrer la casse
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card as="section" padding="p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <x-ui.icon name="shield" class="h-5 w-5" />
                </span>

                <div class="min-w-0">
                    <h2 class="text-lg font-black text-zinc-950">Modifier mon mot de passe</h2>
                    <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                        Votre mot de passe actuel est nécessaire pour valider ce changement.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('scrapyard.account.password.update') }}" class="mt-4 space-y-4">
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
                            class="{{ $passwordInputClass }}"
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
                            class="{{ $passwordInputClass }}"
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
                            class="{{ $passwordInputClass }}"
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

                <x-ui.button as="button" type="submit" variant="secondary" size="lg" class="w-full min-[390px]:w-auto">
                    Modifier mon mot de passe
                </x-ui.button>
            </form>
        </x-ui.card>
    </section>

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
</x-layouts.scrapyard>
