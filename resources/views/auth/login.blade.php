<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Connexion - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="mx-auto w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
                <x-brand-logo :href="route('home')" image-class="h-12 w-auto max-w-[180px] object-contain" />
                <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950">Connexion à Pièce Radar</h1>
                <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                    Accédez à votre espace selon votre compte.
                </p>

                <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="text-sm font-bold text-zinc-700">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                            class="mt-1.5 block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20"
                        >
                        @error('email')
                            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="text-sm font-bold text-zinc-700">Mot de passe</label>
                        <div class="relative mt-1.5">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 pr-12 text-sm font-medium text-zinc-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-[#FC8505] focus:ring-2 focus:ring-[#FC8505]/20"
                            >
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center rounded-r-2xl text-zinc-500 transition hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-inset"
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

                    <label class="flex items-center gap-2 text-sm font-medium text-zinc-700">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="rounded border-zinc-300 text-[#FC8505] focus:ring-[#FC8505]"
                        >
                        Se souvenir de moi
                    </label>

                    <div class="text-right">
                        <a href="{{ route('password.request') }}" class="text-sm font-black text-[#FC8505] hover:text-[#E87804]">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                    >
                        Se connecter
                    </button>
                </form>

                <p class="mt-5 text-center text-sm font-medium text-zinc-600">
                    Nouveau client ?
                    <a href="{{ route('client.register.create') }}" class="font-black text-[#FC8505] hover:text-[#E87804]">Créer un compte client</a>
                </p>
            </section>
        </main>

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
