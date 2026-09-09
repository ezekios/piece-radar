@props([
    'active' => null,
])

@php
    $user = auth()->user();
    $profile = $user?->professionalProfile;
    $unreadNotificationsCount = $user?->unreadNotifications()->count() ?? 0;

    $navigationItems = [
        [
            'key' => 'search',
            'label' => 'Recherche',
            'url' => route('client.parts.index'),
            'active' => $active === 'search' || request()->routeIs('client.parts.*') || request()->routeIs('pieces.*'),
            'icon' => 'parts',
        ],
        [
            'key' => 'saved-searches',
            'label' => 'Mes recherches',
            'url' => route('client.saved-searches.index'),
            'active' => $active === 'saved-searches' || request()->routeIs('client.saved-searches.*'),
            'icon' => 'matches',
        ],
        [
            'key' => 'requests',
            'label' => 'Mes demandes',
            'url' => route('client.requests.index'),
            'active' => $active === 'requests' || request()->routeIs('client.requests.*'),
            'icon' => 'requests',
        ],
        [
            'key' => 'notifications',
            'label' => 'Notifications',
            'url' => route('notifications.index'),
            'active' => $active === 'notifications' || request()->routeIs('notifications.*'),
            'icon' => 'bell',
            'badge' => $unreadNotificationsCount > 0 ? $unreadNotificationsCount : null,
        ],
        [
            'key' => 'account',
            'label' => 'Mon compte',
            'url' => route('professional.account.show'),
            'active' => $active === 'account' || request()->routeIs('professional.account.*'),
            'icon' => 'account',
        ],
    ];

    $mobileActive = match ($active) {
        'requests' => 'requests',
        'account', 'notifications' => 'account',
        default => 'search',
    };
@endphp

<aside class="hidden bg-zinc-950 px-4 py-5 text-white shadow-xl shadow-zinc-950/10 md:fixed md:inset-y-0 md:left-0 md:z-30 md:flex md:w-72 md:flex-col" aria-label="Navigation principale professionnelle">
    <div class="rounded-2xl bg-white p-3 shadow-sm">
        <x-brand-logo :href="route('home')" image-class="h-10 w-auto max-w-[170px] object-contain" />
    </div>

    <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.04] p-4">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">Espace professionnel</p>
        <p class="mt-2 truncate text-sm font-black text-white">{{ $profile?->company_name ?: ($user?->name ?? 'Compte professionnel') }}</p>
        @if ($profile?->city)
            <p class="mt-1 truncate text-xs font-semibold text-zinc-400">{{ $profile->city }}</p>
        @endif
    </div>

    <nav class="mt-6 flex flex-1 flex-col gap-1" aria-label="Sections professionnelles">
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

<x-client.mobile-navigation :active="$mobileActive" />
