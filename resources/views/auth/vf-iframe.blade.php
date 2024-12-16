<x-layouts.app>
    <div>
        <div class="mb-12 mt-6">
            <h1>Intranet</h1>
        </div>

        @if($loginUrl)
            <a class="block bg-neutral-200 w-full p-4 text-sm font-bold rounded text-center" href="{{ $loginUrl }}" target="_blank">
                Klicke hier, um das Intranet zu öffnen.
            </a>
        @endif
    </div>
</x-layouts.app>
