<div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
    @can('tools.teacher')
    <x-tools-card>
        <x-slot name="title">FAQ</x-slot>
        <x-slot name="link">https://dis.section-inf.ch</x-slot>
        <x-slot name="logo">https://media.giphy.com/media/SXS950PdvjSfu9bCpV/giphy-downsized-large.gif</x-slot>

        Questions et réponses de tous les jours sur https://dis.section-inf.ch

        <x-slot name="tags">
            <x-tools-card-tag>PHP</x-tools-card-tag>
        </x-slot>
    </x-tools-card>

    <x-tools-card>
        <x-slot name="title">Raccourcisseur d’URL</x-slot>
        <x-slot name="link">https://ici.section-inf.ch</x-slot>
        <x-slot name="logo">https://media.giphy.com/media/cDtdlPuIGC4UovtVyz/giphy.gif</x-slot>

        De quoi transmettre facilement des ressources sur différents supports à l’adresse https://ici.section-inf.ch/admin

        <x-slot name="tags">
            <x-tools-card-tag>PHP</x-tools-card-tag>
        </x-slot>
    </x-tools-card>
    @endcan

    <x-tools-card>
        <x-slot name="title">IceScrum</x-slot>
        <x-slot name="link">https://etml.icescrum.com</x-slot>
        <x-slot name="logo">https://www.icescrum.com/wp-content/themes/new_icescrum/assets/logo.png</x-slot>

        Outil de gestion de projet.
        <a class="btn btn-sm btn-accent mt-1" target="_blank" href="https://eduvaud.sharepoint.com/:w:/s/msteams_d0db31/EQr2dZ02fwJMjFSKSO9AXS0B66ULFNqBpaYlBfOYlZyV0Q?e=S2PyE9">Aide</a>
        <a class="btn btn-sm btn-accent mt-1" target="_blank" href="https://github.com/XCarrel/IceScrub">Outils</a>

        <x-slot name="tags">
            <x-tools-card-tag>Agile</x-tools-card-tag>
            <x-tools-card-tag>Scrum</x-tools-card-tag>
        </x-slot>
    </x-tools-card>

    <x-tools-card>
        <x-slot name="title">Documentation</x-slot>
        <x-slot name="link">https://enseignement.section-inf.ch</x-slot>
        <x-slot name="logo">https://enseignement.section-inf.ch/images/426.png</x-slot>

        Documentation en libre accès sur les modules ICT sur https://enseignement.section-inf.ch

        <x-slot name="tags">
            <x-tools-card-tag>Python</x-tools-card-tag>
            <x-tools-card-tag>Sphinx</x-tools-card-tag>
            <x-tools-card-tag>Rst</x-tools-card-tag>
        </x-slot>
    </x-tools-card>

    <x-tools-card>
        <x-slot name="title">Git</x-slot>
        <x-slot name="link">https://git.section-inf.ch</x-slot>
        <x-slot name="logo">https://git.section-inf.ch/assets/img/logo.svg</x-slot>

        Un serveur GIT rien que pour nous sur https://git.section-inf.ch ;-)

        <x-slot name="tags">
            <x-tools-card-tag>Gitea</x-tools-card-tag>
            <x-tools-card-tag>Go</x-tools-card-tag>
        </x-slot>
    </x-tools-card>



    <x-tools-card>
        <x-slot name="title">Tutoriels</x-slot>
        <x-slot name="link">https://labs.section-inf.ch</x-slot>
        <x-slot name="logo">https://labs.section-inf.ch/images/icons/dev.png</x-slot>

        Tutoriaux basés sur la plateforme GoogleLabs sur différents modules disponibles à l’adresse https://labs.section-inf.ch/

        <x-slot name="tags">
            <x-tools-card-tag>Go</x-tools-card-tag>
            <x-tools-card-tag>Markdown</x-tools-card-tag>
        </x-slot>
    </x-tools-card>

</div>

