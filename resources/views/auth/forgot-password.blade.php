<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mot de passe oublié - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="mx-auto w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-sm font-black text-[#FC8505]">Pièce Radar</p>
                <h1 class="mt-2 text-2xl font-black leading-tight text-zinc-950">Mot de passe oublié</h1>
                <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                    Saisissez votre email pour recevoir un lien sécurisé de réinitialisation.
                </p>

                @if (session('status'))
                    <div class="mt-4 rounded-2xl border border-orange-200 bg-[#FC8505]/5 p-4 text-sm font-bold text-[#C96504]">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
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

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FC8505] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                    >
                        Envoyer le lien
                    </button>
                </form>

                <p class="mt-5 text-center text-sm font-medium text-zinc-600">
                    <a href="{{ route('login') }}" class="font-black text-[#FC8505] hover:text-[#E87804]">Retour à la connexion</a>
                </p>
            </section>
        </main>
    </body>
</html>
