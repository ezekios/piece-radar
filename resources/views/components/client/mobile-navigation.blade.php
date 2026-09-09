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
            'key' => 'home',
            'label' => 'Accueil',
            'url' => route('home'),
            'icon' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v10h5v-5h4v5h5V10"/>',
        ],
        [
            'key' => 'search',
            'label' => 'Recherche',
            'url' => route('client.parts.index'),
            'icon' => '<circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/>',
        ],
        [
            'key' => 'requests',
            'label' => 'Demandes',
            'url' => $user ? route('client.requests.index') : route('login'),
            'icon' => '<path d="M7 4h10a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2-3-2V6a2 2 0 0 1 2-2Z"/><path d="M8 9h8"/><path d="M8 13h6"/>',
        ],
        [
            'key' => 'account',
            'label' => 'Compte',
            'url' => $accountUrl,
            'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>',
        ],
    ];
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-zinc-200 bg-white/95 px-3 py-2 shadow-[0_-4px_16px_rgba(24,24,27,0.06)] backdrop-blur sm:hidden" aria-label="Navigation mobile client">
    <div class="mx-auto grid max-w-md grid-cols-4 gap-1 text-center">
        @foreach ($items as $item)
            @php($isActive = $active === $item['key'])

            <a
                href="{{ $item['url'] }}"
                class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1.5 py-1 text-[11px] font-bold transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 {{ $isActive ? 'bg-[#FC8505]/10 text-[#C96504]' : 'text-zinc-500 hover:text-[#FC8505]' }}"
                @if ($isActive) aria-current="page" @endif
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    {!! $item['icon'] !!}
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
