<div>
    <h1>{{ $event->title }}</h1>

    <div class="max-w-4xl mx-auto space-y-2 mt-8">
        @foreach($event->slots()->orderBy('start_time', 'asc')->get() as $slot)
            <label for="enrollment_{{ $slot->id }}"
                   class="flex items-center justify-between rounded border p-4 bg-gray-50  @if($event->enrollment?->event_slot_id == $slot->id) ring ring-green-500 border-green-500 @endif @if($slot->free_seats <= 0 || $slot->start_time->isPast()) opacity-50 cursor-not-allowed @else hover:ring ring-gray-100 @endif">
                <span class="flex items-center space-x-4">
                    <input type="radio"
                           name="enrollment_{{ $event->id }}"
                           id="enrollment_{{ $slot->id }}"
                           class="text-green-500"
                           wire:change="enroll('{{ $slot->id }}')"
                           @if($slot->free_seats <= 0 || $slot->start_time->isPast()) disabled @endif
                           @if($event->enrollment?->event_slot_id == $slot->id) checked @endif>
                    <span class="font-bold">{{ $slot->start_time->format('d.m.Y H:i') }}</span>
                </span>

                <span class="text-gray-700 text-sm">
                    {{ $slot->free_seats }} Plätze frei
                </span>
            </label>
        @endforeach
    </div>

</div>
