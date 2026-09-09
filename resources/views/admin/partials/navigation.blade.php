@php
    $user = auth()->user();

    $navigationItems = [
        [
            'label' => 'Tableau de bord',
            'url' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.dashboard'),
            'icon' => 'dashboard',
        ],
        [
            'label' => 'Utilisateurs',
            'url' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
            'icon' => 'users',
        ],
        [
            'label' => 'Casses',
            'url' => route('admin.scrapyards.index'),
            'active' => request()->routeIs('admin.scrapyards.*'),
            'icon' => 'building',
        ],
    ];
@endphp

<aside class="hidden bg-zinc-950 px-4 py-5 text-white shadow-xl shadow-zinc-950/10 md:fixed md:inset-y-0 md:left-0 md:z-30 md:flex md:w-72 md:flex-col" aria-label="Navigation principale admin">
    <div class="rounded-2xl bg-white p-3 shadow-sm">
        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[170px] object-contain" />
    </div>

    <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.04] p-4">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">Espace admin</p>
        <p class="mt-2 truncate text-sm font-black text-white">{{ $user?->name ?? 'Administrateur' }}</p>
        <p class="mt-1 truncate text-xs font-semibold text-zinc-400">Administration Pièce Radar</p>
    </div>

    <nav class="mt-6 flex flex-1 flex-col gap-1" aria-label="Sections admin">
        @foreach ($navigationItems as $item)
            <a
                href="{{ $item['url'] }}"
                @if ($item['active']) aria-current="page" @endif
                class="group flex min-h-11 items-center gap-3 rounded-2xl px-3 py-2 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 focus:ring-offset-zinc-950 {{ $item['active'] ? 'bg-[#FC8505] text-white shadow-sm' : 'text-zinc-300 hover:bg-white/5 hover:text-white' }}"
            >
                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $item['active'] ? 'bg-white/15 text-white' : 'bg-white/5 text-zinc-400 group-hover:text-white' }}">
                    <x-ui.icon :name="$item['icon']" class="h-4 w-4" />
                </span>
                <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mt-5 border-t border-white/10 pt-4">
        <div class="mb-3 flex items-center justify-between gap-3 rounded-2xl bg-white/[0.04] p-3">
            <span class="text-sm font-black text-zinc-300">Thème</span>
            <x-ui.theme-toggle class="border-white/10 bg-white/5 text-zinc-300 hover:border-[#FC8505]/40 dark:border-white/10 dark:bg-white/5" />
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="flex min-h-11 w-full items-center gap-3 rounded-2xl px-3 py-2 text-sm font-black text-zinc-300 transition hover:bg-white/5 hover:text-white focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 focus:ring-offset-zinc-950"
            >
                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/5 text-zinc-400">
                    <x-ui.icon name="logout" class="h-4 w-4" />
                </span>
                Déconnexion
            </button>
        </form>
    </div>
</aside>

<nav class="relative z-40 md:hidden" aria-label="Navigation administrateur mobile">
    <div class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[150px] object-contain" theme-aware />

        <x-ui.badge variant="orange" class="ml-auto hidden shrink-0 min-[375px]:inline-flex">
            Administrateur
        </x-ui.badge>

        <details class="group relative shrink-0">
            <summary
                class="inline-flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-800 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:focus:ring-offset-zinc-950 [&::-webkit-details-marker]:hidden"
                aria-label="Ouvrir ou fermer le menu mobile admin"
            >
                <x-ui.icon name="menu" class="h-5 w-5" />
            </summary>

            <div class="absolute right-0 top-full z-50 mt-2 w-[min(calc(100vw-1.5rem),22rem)] overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl shadow-zinc-950/10 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 bg-zinc-950 p-4 text-white dark:border-zinc-800">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">Espace admin</p>
                    <p class="mt-2 truncate text-sm font-black">{{ $user?->name ?? 'Administrateur' }}</p>
                    <p class="mt-1 truncate text-xs font-semibold text-zinc-400">Administration Pièce Radar</p>
                </div>

                <div class="grid gap-1 p-2">
                    @foreach ($navigationItems as $item)
                        <a
                            href="{{ $item['url'] }}"
                            @if ($item['active']) aria-current="page" @endif
                            class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950 {{ $item['active'] ? 'bg-[#FC8505] text-white shadow-sm' : 'text-zinc-700 hover:bg-[#FC8505]/10 hover:text-[#C96504] dark:text-zinc-300 dark:hover:bg-white/5 dark:hover:text-white' }}"
                        >
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $item['active'] ? 'bg-white/15 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                <x-ui.icon :name="$item['icon']" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="border-t border-zinc-100 p-2 dark:border-zinc-800">
                    <div class="mb-1 flex min-h-11 items-center justify-between gap-3 rounded-xl px-3 py-2">
                        <span class="text-sm font-black text-zinc-700 dark:text-zinc-300">Thème</span>
                        <x-ui.theme-toggle />
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-black text-zinc-700 transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-200 focus:ring-offset-2 dark:text-zinc-300 dark:hover:bg-red-950/40 dark:hover:text-red-300 dark:focus:ring-offset-zinc-950"
                        >
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <x-ui.icon name="logout" class="h-4 w-4" />
                            </span>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </details>
    </div>
</nav>
