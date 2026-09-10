<x-layouts.auth title="Compte en attente - Pièce Radar" card-width="max-w-xl">
    <x-slot:heading>
        Votre compte professionnel est en attente de validation.
    </x-slot:heading>

    <x-slot:description>
        Votre email est vérifié, mais l'accès métier reste bloqué tant qu'un administrateur n'a pas activé votre casse.
    </x-slot:description>

    <div class="mt-5 text-center">
        <x-ui.badge variant="orange">Validation en attente</x-ui.badge>
    </div>

    <p class="mt-4 text-center text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400">
        Votre inscription a bien été enregistrée. Un administrateur doit valider votre casse avant l'accès complet à Pièce Radar.
    </p>

    @if ($scrapyard)
        <x-ui.card variant="subtle" padding="p-4" class="mt-5 text-left">
            <p class="text-xs font-black uppercase text-zinc-500 dark:text-zinc-400">Casse enregistrée</p>
            <p class="mt-1 text-lg font-black text-zinc-950 dark:text-zinc-50">{{ $scrapyard->name }}</p>
            <p class="mt-1 text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ $scrapyard->city ?: 'Ville non renseignée' }}</p>

            <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div class="rounded-xl bg-white p-3 ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
                    <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400">SIRET déclaré</p>
                    <p class="mt-1 font-black text-zinc-950 dark:text-zinc-50">{{ $scrapyard->siret ?: 'Non renseigné' }}</p>
                </div>

                <div class="rounded-xl bg-white p-3 ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
                    <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Statut email</p>
                    <p class="mt-1 font-black text-emerald-700 dark:text-emerald-300">Email vérifié</p>
                </div>
            </div>
        </x-ui.card>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-5">
        @csrf

        <x-ui.button as="button" type="submit" variant="secondary" class="w-full sm:w-auto">
            Se déconnecter
        </x-ui.button>
    </form>
</x-layouts.auth>
