<nav class="rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm" aria-label="Navigation administrateur">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-3 py-2 text-sm font-black transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#FC8505]/10 text-[#C96504]' : 'text-zinc-700 hover:bg-zinc-50 hover:text-[#FC8505]' }}">
            Tableau de bord
        </a>
        <a href="{{ route('admin.users.index') }}" class="rounded-xl px-3 py-2 text-sm font-black transition {{ request()->routeIs('admin.users.*') ? 'bg-[#FC8505]/10 text-[#C96504]' : 'text-zinc-700 hover:bg-zinc-50 hover:text-[#FC8505]' }}">
            Utilisateurs
        </a>
        <a href="{{ route('admin.scrapyards.index') }}" class="rounded-xl px-3 py-2 text-sm font-black transition {{ request()->routeIs('admin.scrapyards.*') ? 'bg-[#FC8505]/10 text-[#C96504]' : 'text-zinc-700 hover:bg-zinc-50 hover:text-[#FC8505]' }}">
            Casses
        </a>
        <a href="{{ route('home') }}" class="rounded-xl px-3 py-2 text-sm font-black text-zinc-700 transition hover:bg-zinc-50 hover:text-[#FC8505]">
            Accueil
        </a>
        <form method="POST" action="{{ route('logout') }}" class="ml-auto">
            @csrf
            <button type="submit" class="rounded-xl px-3 py-2 text-sm font-black text-zinc-700 transition hover:bg-zinc-50 hover:text-[#FC8505]">
                Déconnexion
            </button>
        </form>
    </div>
</nav>
