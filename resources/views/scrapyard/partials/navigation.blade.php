@php
    $variant = $variant ?? 'horizontal';
    $isSidebar = $variant === 'sidebar';
    $user = auth()->user();
    $scrapyard = $user?->scrapyard;
    $unreadNotificationsCount = $user?->unreadNotifications()->count() ?? 0;

    $navigationItems = [
        [
            'label' => 'Tableau de bord',
            'url' => route('scrapyard.dashboard'),
            'active' => request()->routeIs('scrapyard.dashboard'),
            'icon' => 'dashboard',
        ],
        [
            'label' => 'Véhicules',
            'url' => route('scrapyard.vehicles.index'),
            'active' => request()->routeIs('scrapyard.vehicles.*'),
            'icon' => 'vehicles',
        ],
        [
            'label' => 'Pièces',
            'url' => route('scrapyard.parts.index'),
            'active' => request()->routeIs('scrapyard.parts.*'),
            'icon' => 'parts',
        ],
        [
            'label' => 'Demandes',
            'url' => route('scrapyard.requests.index'),
            'active' => request()->routeIs('scrapyard.requests.*'),
            'icon' => 'requests',
        ],
        [
            'label' => 'Correspondances',
            'url' => route('scrapyard.correspondences.index'),
            'active' => request()->routeIs('scrapyard.correspondences.*'),
            'icon' => 'matches',
        ],
        [
            'label' => 'Notifications',
            'url' => route('notifications.index'),
            'active' => request()->routeIs('notifications.*'),
            'icon' => 'bell',
            'badge' => $unreadNotificationsCount > 0 ? $unreadNotificationsCount : null,
        ],
        [
            'label' => 'Mon compte',
            'url' => route('scrapyard.account.show'),
            'active' => request()->routeIs('scrapyard.account.*'),
            'icon' => 'account',
        ],
    ];
@endphp

@if ($isSidebar)
    <aside class="hidden md:fixed md:inset-y-0 md:left-0 md:z-30 md:flex md:w-72 md:flex-col bg-zinc-950 px-4 py-5 text-white shadow-xl shadow-zinc-950/10" aria-label="Navigation principale casse">
        <div class="rounded-2xl bg-white p-3 shadow-sm">
            <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[170px] object-contain" />
        </div>

        <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">Espace casse</p>
            <p class="mt-2 truncate text-sm font-black text-white">{{ $scrapyard?->name ?? $user?->name ?? 'Compte casse' }}</p>
            @if ($scrapyard?->city)
                <p class="mt-1 truncate text-xs font-semibold text-zinc-400">{{ $scrapyard->city }}</p>
            @endif
        </div>

        <nav class="mt-6 flex flex-1 flex-col gap-1" aria-label="Sections casse">
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

                    @if (! empty($item['badge']))
                        <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-black text-zinc-950">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="mt-5 border-t border-white/10 pt-4">
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
    </aside>

    <nav class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm md:hidden" aria-label="Navigation casse mobile">
        <div class="flex gap-2 overflow-x-auto pb-1 pr-8 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @foreach ($navigationItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    @if ($item['active']) aria-current="page" @endif
                    class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-3 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 {{ $item['active'] ? 'border-[#FC8505]/30 bg-[#FC8505]/10 text-[#C96504]' : 'border-zinc-200 bg-white text-zinc-700 hover:border-orange-200 hover:text-[#FC8505]' }}"
                >
                    <x-ui.icon :name="$item['icon']" class="h-4 w-4" />
                    {{ $item['label'] }}
                    @if (! empty($item['badge']))
                        <span class="rounded-full bg-[#FC8505] px-1.5 py-0.5 text-[10px] leading-none text-white">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach

            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf

                <button
                    type="submit"
                    class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
                >
                    <x-ui.icon name="logout" class="h-4 w-4" />
                    Déconnexion
                </button>
            </form>
        </div>

        <div class="pointer-events-none absolute inset-y-2 right-2 w-8 rounded-r-xl bg-gradient-to-l from-white to-white/0" aria-hidden="true"></div>
    </nav>
@else
<nav class="mt-3 rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm" aria-label="Navigation casse">
    <div class="flex gap-2 overflow-x-auto pb-1 sm:flex-wrap sm:overflow-visible sm:pb-0">
        @foreach ($navigationItems as $item)
            <a
                href="{{ $item['url'] }}"
                @if ($item['active']) aria-current="page" @endif
                class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border px-3 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 {{ $item['active'] ? 'border-[#FC8505]/30 bg-[#FC8505]/10 text-[#C96504]' : 'border-zinc-200 bg-white text-zinc-700 hover:border-orange-200 hover:text-[#FC8505]' }}"
            >
                {{ $item['label'] }}
                @if (! empty($item['badge']))
                    <span class="ml-2 rounded-full bg-[#FC8505] px-1.5 py-0.5 text-[10px] leading-none text-white">
                        {{ $item['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach

        <form method="POST" action="{{ route('logout') }}" class="shrink-0 sm:ml-auto">
            @csrf

            <button
                type="submit"
                class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
            >
                Déconnexion
            </button>
        </form>
    </div>
</nav>
@endif
