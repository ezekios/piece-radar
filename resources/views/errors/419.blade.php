<x-layouts.auth title="Session expirée - Pièce Radar" card-width="max-w-md">
    <x-slot:heading>
        Votre session a expiré.
    </x-slot:heading>

    <x-slot:description>
        Veuillez vous reconnecter pour continuer.
    </x-slot:description>

    <div class="mt-5 text-center">
        <x-ui.badge variant="orange">Erreur 419</x-ui.badge>
    </div>

    <x-ui.button :href="route('login')" class="mt-5 w-full">
        Retour à la connexion
    </x-ui.button>
</x-layouts.auth>
