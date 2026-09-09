<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Notifications - Pièce Radar</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8F7F4] font-sans text-zinc-950 antialiased">
        @php
            $user = auth()->user();
            $isClient = $user?->role === 'client';
            $isScrapyard = $user?->role === 'scrapyard';
            $displayTimezone = config('app.display_timezone', 'UTC');
        @endphp

        <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-20 pt-5 sm:px-6 sm:pb-10 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <header class="border-b border-zinc-200/80 pb-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[160px] object-contain" />

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($isClient)
                                <a href="{{ route('client.saved-searches.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                    Mes recherches
                                </a>
                                <a href="{{ route('client.requests.index') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                    Mes demandes
                                </a>
                                <a href="{{ route('client.account.show') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                    Mon compte
                                </a>
                            @elseif ($isScrapyard)
                                <a href="{{ route('scrapyard.dashboard') }}" class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#FC8505] ring-1 ring-orange-100 hover:text-[#E87804]">
                                    Espace casse
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-full bg-white px-3 py-1 text-xs font-black text-zinc-600 ring-1 ring-zinc-200 hover:text-zinc-900">
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-black leading-tight text-zinc-950 sm:text-3xl">Notifications</h1>
                            <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">
                                {{ $notifications->count() }} notification{{ $notifications->count() > 1 ? 's' : '' }}
                            </p>
                        </div>

                        @if ($notifications->whereNull('read_at')->isNotEmpty())
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm font-black text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                    Tout marquer comme lu
                                </button>
                            </form>
                        @endif
                    </div>
                </header>

                @if (session('success'))
                    <div class="mt-4 rounded-2xl border border-orange-200 bg-white p-4 text-sm font-bold text-[#C96504] shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($notifications->isEmpty())
                    <section class="mt-5 rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm">
                        <h2 class="text-base font-black text-zinc-950">Aucune notification.</h2>
                        <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                            Les informations importantes liées à vos recherches et demandes apparaîtront ici.
                        </p>
                    </section>
                @else
                    <section class="mt-5 space-y-3">
                        @foreach ($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $createdAtDisplay = $notification->created_at?->copy()->timezone($displayTimezone);
                            @endphp

                            <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm {{ $notification->read_at ? '' : 'ring-1 ring-orange-100' }}">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="text-base font-black text-zinc-950">
                                                {{ $data['title'] ?? 'Notification' }}
                                            </h2>

                                            @if (! $notification->read_at)
                                                <span class="rounded-full bg-[#FC8505]/10 px-2.5 py-1 text-[11px] font-black text-[#C96504]">
                                                    Non lue
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-2 text-sm font-medium leading-6 text-zinc-700">
                                            {{ $data['message'] ?? '' }}
                                        </p>

                                        <p class="mt-2 text-xs font-bold text-zinc-500">
                                            {{ $createdAtDisplay?->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-col gap-2 border-t border-zinc-100 pt-3 sm:flex-row sm:items-center sm:justify-between">
                                    @if (! empty($data['url']))
                                        <a href="{{ route('notifications.open', $notification) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#FC8505] px-4 py-2 text-sm font-black text-white shadow-sm transition hover:bg-[#E87804] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                                            Ouvrir
                                        </a>
                                    @endif

                                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Supprimer cette notification ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm font-black text-zinc-700 shadow-sm transition hover:border-red-200 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 sm:w-auto">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>
        </main>

        @if ($isClient)
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
                    <a href="{{ route('client.account.show') }}" class="text-zinc-500">
                        Compte
                    </a>
                </div>
            </nav>
        @endif
    </body>
</html>
