<nav class="rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm" aria-label="Navigation administrateur">
    <div class="flex gap-2 overflow-x-auto pb-1 sm:flex-wrap sm:overflow-visible sm:pb-0">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border px-3 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 {{ request()->routeIs('admin.dashboard') ? 'border-[#FC8505]/30 bg-[#FC8505]/10 text-[#C96504]' : 'border-zinc-200 bg-white text-zinc-700 hover:border-orange-200 hover:text-[#FC8505]' }}">
            Tableau de bord
        </a>
        <a href="{{ route('admin.users.index') }}" class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border px-3 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 {{ request()->routeIs('admin.users.*') ? 'border-[#FC8505]/30 bg-[#FC8505]/10 text-[#C96504]' : 'border-zinc-200 bg-white text-zinc-700 hover:border-orange-200 hover:text-[#FC8505]' }}">
            Utilisateurs
        </a>
        <a href="{{ route('admin.scrapyards.index') }}" class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border px-3 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 {{ request()->routeIs('admin.scrapyards.*') ? 'border-[#FC8505]/30 bg-[#FC8505]/10 text-[#C96504]' : 'border-zinc-200 bg-white text-zinc-700 hover:border-orange-200 hover:text-[#FC8505]' }}">
            Casses
        </a>
        <a href="{{ route('home') }}" class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
            Accueil
        </a>
        <form method="POST" action="{{ route('logout') }}" class="shrink-0 sm:ml-auto">
            @csrf
            <button type="submit" class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2">
                Déconnexion
            </button>
        </form>
    </div>
</nav>
