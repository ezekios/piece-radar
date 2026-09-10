<x-layouts.auth title="Accès non autorisé - Pièce Radar" card-width="max-w-md">
    <x-slot:heading>
        Accès non autorisé
    </x-slot:heading>

    <x-slot:description>
        Vous n'avez pas l'autorisation d'accéder à cette page.
    </x-slot:description>

    <div class="mt-5 text-center">
        <x-ui.badge variant="orange">Erreur 403</x-ui.badge>
    </div>

    <x-ui.button :href="route('home')" class="mt-5 w-full">
        Retour à l'accueil
    </x-ui.button>
</x-layouts.auth>
