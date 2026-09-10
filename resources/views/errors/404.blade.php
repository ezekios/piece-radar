<x-layouts.auth title="Page introuvable - Pièce Radar" card-width="max-w-md">
    <x-slot:heading>
        Page introuvable
    </x-slot:heading>

    <x-slot:description>
        La page que vous recherchez n'existe pas ou n'est plus disponible.
    </x-slot:description>

    <div class="mt-5 text-center">
        <x-ui.badge variant="orange">Erreur 404</x-ui.badge>
    </div>

    <x-ui.button :href="route('home')" class="mt-5 w-full">
        Retour à l'accueil
    </x-ui.button>
</x-layouts.auth>
