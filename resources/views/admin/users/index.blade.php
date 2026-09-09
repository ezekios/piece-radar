<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Utilisateurs - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $roleLabels = [
                'client' => 'Client',
                'professional' => 'Garage / Mécanicien',
                'scrapyard' => 'Casse',
                'admin' => 'Administrateur',
            ];
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-6xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="space-y-5">
                <header class="space-y-4 border-b border-zinc-200/80 pb-5">
                    <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />
                    @include('admin.partials.navigation')

                    <div>
                        <h1 class="text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Utilisateurs</h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            {{ $users->count() }} compte{{ $users->count() > 1 ? 's' : '' }} enregistré{{ $users->count() > 1 ? 's' : '' }}.
                        </p>
                    </div>
                </header>

                <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <div class="grid gap-0 divide-y divide-zinc-100">
                        @foreach ($users as $user)
                            <article class="grid gap-3 p-4 sm:grid-cols-[1.2fr_1.4fr_0.9fr_0.9fr] sm:items-center">
                                <div>
                                    <p class="text-xs font-black uppercase text-zinc-500">Nom</p>
                                    <p class="mt-1 font-black text-zinc-950">{{ $user->name }}</p>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase text-zinc-500">Email</p>
                                    <p class="mt-1 break-words font-semibold text-zinc-700">{{ $user->email }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase text-zinc-500">Rôle</p>
                                    <p class="mt-1 font-black text-[#FC8505]">{{ $roleLabels[$user->role] ?? $user->role }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase text-zinc-500">Créé le</p>
                                    <p class="mt-1 font-semibold text-zinc-700">{{ $user->created_at?->format('d/m/Y') }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
