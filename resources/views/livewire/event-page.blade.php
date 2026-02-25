<div>
    <h1>{{ $event->title }}</h1>

    <div class="max-w-4xl mx-auto space-y-2 mt-8">
        @if($enrolledTo)
            <div class="mb-8">
                <div class="rounded-lg  px-12 py-6 bg-green-300 flex items-center space-x-8">
                    <x-filament::icon icon="heroicon-s-check-circle" class="w-12 h-12 text-green-700"/>
                    <span class="text-green-700 text-lg font-bold">
                        Deine Anmeldung zu {{ $event->title }} wurde hinterlegt!
                    </span>
                </div>
            </div>
        @endif

        @foreach($event->slots()->orderBy('start_time', 'asc')->get() as $slot)
            <label for="enrollment_{{ $slot->id }}"
                   class="flex items-center justify-between rounded border p-4 bg-gray-50  @if($enrolledTo == $slot->id) ring ring-green-500 border-green-500 @endif @if($slot->free_seats <= 0 || $slot->start_time->isPast()) opacity-50 cursor-not-allowed @else hover:ring ring-gray-100 @endif">
                <span class="flex items-center space-x-4">
                    <input type="radio"
                           name="enrollment_{{ $event->id }}"
                           id="enrollment_{{ $slot->id }}"
                           class="text-green-500"
                           data-umami-event="event_slot_selected"
                           data-umami-event-slot_state="{{ $slot->start_time->isPast() || $slot->free_seats <= 0 ? 'closed' : 'open' }}"
                           value="{{ $slot->id }}"
                           wire:model.live="enrolledTo"
                           @if($slot->free_seats <= 0 || $slot->start_time->isPast()) disabled @endif
                    >
                    <span class="font-bold">{{ $slot->start_time->format('d.m.Y H:i') }} Uhr</span>
                </span>

                <span class="text-gray-700 text-sm">
                    {{ $slot->free_seats }} Plätze frei
                </span>
            </label>
        @endforeach
    </div>

    @if($enrolledTo)
        <div class="mt-8 p-8 text-center border-t">
            <x-filament::link
                color="danger"
                wire:click="deleteEnrollment()"
                wire:confirm="Möchtest du wirklich deine Anmeldung löschen?"
                tag="button"
                data-umami-event="event_enrollment_delete_clicked"
            >
                Meine Anmeldung löschen
            </x-filament::link>
        </div>
    @endif

</div>
