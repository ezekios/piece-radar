@props(['active' => null])

@php
    $user = auth()->user();

    $accountUrl = match ($user?->role) {
        'professional' => route('professional.account.show'),
        'scrapyard' => route('scrapyard.dashboard'),
        'admin' => route('admin.dashboard'),
        default => $user ? route('client.account.show') : route('login'),
    };

    $items = [
        [
            'key' => 'search',
            'label' => 'Recherche',
            'url' => route('client.parts.index'),
            'icon' => '<circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/>',
        ],
        [
            'key' => 'saved-searches',
            'label' => 'Recherches',
            'url' => $user ? route('client.saved-searches.index') : route('login'),
            'icon' => '<path d="M5 5h14v14l-7-4-7 4V5Z"/><path d="M9 9h6"/><path d="M9 12h4"/>',
        ],
        [
            'key' => 'requests',
            'label' => 'Demandes',
            'url' => $user ? route('client.requests.index') : route('login'),
            'icon' => '<path d="M7 4h10a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2-3-2V6a2 2 0 0 1 2-2Z"/><path d="M8 9h8"/><path d="M8 13h6"/>',
        ],
        [
            'key' => 'notifications',
            'label' => 'Notifs',
            'url' => $user ? route('notifications.index') : route('login'),
            'icon' => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',
        ],
        [
            'key' => 'account',
            'label' => 'Compte',
            'url' => $accountUrl,
            'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>',
        ],
    ];
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-zinc-200 bg-white/95 px-3 py-2 shadow-[0_-4px_16px_rgba(24,24,27,0.06)] backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:hidden" aria-label="Navigation mobile client">
    <div class="mx-auto grid max-w-md {{ $user ? 'grid-cols-6' : 'grid-cols-5' }} gap-0.5 text-center min-[430px]:gap-1">
        @foreach ($items as $item)
            @php($isActive = $active === $item['key'])

            <a
                href="{{ $item['url'] }}"
                class="flex min-h-12 min-w-0 flex-col items-center justify-center gap-0.5 rounded-xl px-0.5 py-1 text-[10px] font-bold leading-none transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:focus:ring-offset-zinc-950 min-[390px]:px-1 min-[430px]:gap-1 min-[430px]:text-[11px] {{ $isActive ? 'bg-[#FC8505]/10 text-[#C96504] dark:text-orange-200' : 'text-zinc-500 hover:text-[#FC8505] dark:text-zinc-400 dark:hover:text-[#FC8505]' }}"
                @if ($isActive) aria-current="page" @endif
            >
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    {!! $item['icon'] !!}
                </svg>
                <span class="block max-w-full whitespace-nowrap">{{ $item['label'] }}</span>
            </a>
        @endforeach

        @if ($user)
            <form method="POST" action="{{ route('logout') }}" class="min-w-0">
                @csrf
                <button
                    type="submit"
                    class="flex min-h-12 w-full min-w-0 flex-col items-center justify-center gap-0.5 rounded-xl px-0.5 py-1 text-[10px] font-bold leading-none text-zinc-500 transition hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:text-zinc-400 dark:focus:ring-offset-zinc-950 dark:hover:text-[#FC8505] min-[390px]:px-1 min-[430px]:gap-1 min-[430px]:text-[11px]"
                >
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 17l5-5-5-5"/>
                        <path d="M15 12H3"/>
                        <path d="M15 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/>
                    </svg>
                    <span class="block max-w-full whitespace-nowrap">Sortir</span>
                </button>
            </form>
        @endif
    </div>
</nav>
