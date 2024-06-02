<div>
    <h1>LfV-Chat</h1>

    <p class="text-center my-10">
        Die LfV Greven betreibt einen eigenen Chat-Server, der mit Standort
        in Deutschland eine gesicherte Chat-Umgebung für unseren Verein ermöglicht.
    </p>

    <h2 class="font-bold text-xl underline mb-2">1. App-Download:</h2>
    <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 justify-around">
        <a href="https://apps.apple.com/de/app/mattermost/id1257222717?itsct=apps_box_link&itscg=30200" target="_blank">
            <img class="h-14" src="{{ Vite::asset('resources/images/app-stores/ios.svg') }}" alt="iOS-Download">
        </a>

        <a href="https://play.google.com/store/apps/details?id=com.mattermost.rn" target="_blank">
            <img class="h-14" src="{{ Vite::asset('resources/images/app-stores/android.svg') }}" alt="iOS-Download">
        </a>
    </diV>

    <h2 class="font-bold text-xl underline mb-2 mt-10">2. Chat-Server verbinden</h2>
    <table>
        <tbody>
            <tr>
                <th class="pr-4">Server-URL:</th>
                <td>chat.sportflugzentrum.de</td>
            </tr>
            <tr>
                <th class="pr-4">Anzeigename:</th>
                <td>LfV-Greven</td>
            </tr>
        </tbody>
    </table>

    <h2 class="font-bold text-xl underline mb-2 mt-10">3. Deine Login-Daten</h2>
    <table>
        <tbody>
            <tr>
                <th class="pr-4">E-Mail-Adresse:</th>
                <td>{{ auth()->user()->email }}</td>
            </tr>
            <tr>
                <th class="pr-4">Passwort:</th>
                <td>
                    ••••••

                    <button wire:click="$dispatch('openModal', {component: 'chat.reset-passwort-modal'})" class="pl-4 text-xs underline text-primary">
                        zurücksetzen
                    </button>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="mt-12 text-center">
        <a href="{{ route('home') }}" class="link" wire:navigate>
            zurück
        </a>
    </div>
</div>
