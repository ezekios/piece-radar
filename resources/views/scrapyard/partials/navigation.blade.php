@php
    $navigationItems = [
        [
            'label' => 'Tableau de bord',
            'url' => route('scrapyard.dashboard'),
            'active' => request()->routeIs('scrapyard.dashboard'),
        ],
        [
            'label' => 'Véhicules',
            'url' => route('scrapyard.vehicles.index'),
            'active' => request()->routeIs('scrapyard.vehicles.*'),
        ],
        [
            'label' => 'Pièces',
            'url' => route('scrapyard.parts.index'),
            'active' => request()->routeIs('scrapyard.parts.*'),
        ],
        [
            'label' => 'Demandes',
            'url' => route('scrapyard.requests.index'),
            'active' => request()->routeIs('scrapyard.requests.*'),
        ],
    ];
@endphp

<nav class="mt-3 flex flex-wrap gap-2 rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm" aria-label="Navigation casse">
    @foreach ($navigationItems as $item)
        <a
            href="{{ $item['url'] }}"
            class="inline-flex h-10 items-center justify-center rounded-xl border px-3 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 {{ $item['active'] ? 'border-[#FC8505]/30 bg-[#FC8505]/10 text-[#C96504]' : 'border-zinc-200 bg-white text-zinc-700 hover:border-orange-200 hover:text-[#FC8505]' }}"
        >
            {{ $item['label'] }}
        </a>
    @endforeach

    <form method="POST" action="{{ route('logout') }}" class="ml-auto">
        @csrf

        <button
            type="submit"
            class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2"
        >
            Déconnexion
        </button>
    </form>
</nav>
