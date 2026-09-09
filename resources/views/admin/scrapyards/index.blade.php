<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Casses - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-6xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="space-y-5">
                <header class="space-y-4 border-b border-zinc-200/80 pb-5">
                    <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />
                    @include('admin.partials.navigation')

                    <div>
                        <h1 class="text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Casses</h1>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                            {{ $scrapyards->count() }} casse{{ $scrapyards->count() > 1 ? 's' : '' }} référencée{{ $scrapyards->count() > 1 ? 's' : '' }}.
                        </p>
                    </div>
                </header>

                @if (session('success'))
                    <div class="rounded-2xl border border-orange-200 bg-white p-4 text-sm font-bold text-[#C96504] shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-2xl border border-red-200 bg-white p-4 text-sm font-bold text-red-700 shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <section class="grid gap-3">
                    @foreach ($scrapyards as $scrapyard)
                        @php
                            $emailVerified = (bool) $scrapyard->user?->hasVerifiedEmail();
                        @endphp

                        <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-base font-black text-zinc-950">{{ $scrapyard->name }}</h2>
                                    <p class="mt-1 text-sm font-semibold text-zinc-600">{{ $scrapyard->city ?: 'Ville non renseignée' }}</p>
                                </div>

                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $scrapyard->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-[#FC8505]/10 text-[#C96504]' }}">
                                    {{ $scrapyard->is_active ? 'Active' : 'En attente' }}
                                </span>
                            </div>

                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                                <div class="rounded-xl bg-zinc-50 p-3">
                                    <dt class="text-xs font-bold text-zinc-500">Email</dt>
                                    <dd class="mt-1 break-words font-black text-zinc-950">{{ $scrapyard->email ?: $scrapyard->user?->email ?: 'Non renseigné' }}</dd>
                                </div>
                                <div class="rounded-xl bg-zinc-50 p-3">
                                    <dt class="text-xs font-bold text-zinc-500">Statut email</dt>
                                    <dd class="mt-1 font-black {{ $emailVerified ? 'text-emerald-700' : 'text-[#C96504]' }}">
                                        {{ $emailVerified ? 'Vérifié' : 'Non vérifié' }}
                                    </dd>
                                </div>
                                <div class="rounded-xl bg-zinc-50 p-3">
                                    <dt class="text-xs font-bold text-zinc-500">SIRET déclaré</dt>
                                    <dd class="mt-1 font-black text-zinc-950">{{ $scrapyard->siret ?: 'Non renseigné' }}</dd>
                                </div>
                                <div class="rounded-xl bg-zinc-50 p-3">
                                    <dt class="text-xs font-bold text-zinc-500">Téléphone</dt>
                                    <dd class="mt-1 font-black text-zinc-950">{{ $scrapyard->phone ?: 'Non renseigné' }}</dd>
                                </div>
                                <div class="rounded-xl bg-zinc-50 p-3">
                                    <dt class="text-xs font-bold text-zinc-500">Responsable</dt>
                                    <dd class="mt-1 font-black text-zinc-950">{{ $scrapyard->user?->name ?: 'Non renseigné' }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4 border-t border-zinc-100 pt-3">
                                <form method="POST" action="{{ route('admin.scrapyards.update-status', $scrapyard) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $scrapyard->is_active ? '0' : '1' }}">
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl {{ $scrapyard->is_active ? 'border border-zinc-200 bg-white text-zinc-700 hover:border-orange-200 hover:text-[#FC8505]' : 'bg-[#FC8505] text-white hover:bg-[#E87804]' }} px-4 py-2 text-sm font-black shadow-sm transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                        {{ $scrapyard->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </section>
            </div>
        </main>
    </body>
</html>
