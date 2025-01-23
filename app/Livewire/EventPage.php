<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\EventSlot;
use Filament\Notifications\Notification;
use Livewire\Component;

class EventPage extends Component
{
    public Event $event;

    public function enroll($id)
    {
        // Recheck free seats
        $slot = EventSlot::find($id);
        abort_unless($slot->free_seats > 0, 403);

        // Seats available
        EventEnrollment::updateOrCreate(
            ['user_id' => auth()->id()],
            ['event_slot_id' => $slot->id],
        );

        Notification::make()
            ->success()
            ->title('Erfolg')
            ->body('Deine Anmeldung wurde gespeichert.')
            ->send();
    }

    public function render()
    {
        return view('livewire.event-page');
    }
}
