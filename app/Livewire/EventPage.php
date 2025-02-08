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
    public ?string $enrolledTo;

    public function mount()
    {
        $this->enrolledTo = $this->event->enrollment?->event_slot_id;
    }

    public function updatedEnrolledTo($id)
    {
        // Recheck free seats
        $slot = EventSlot::find($id);
        abort_unless($slot->free_seats > 0, 403);
        abort_if($slot->start_time->isPast() > 0, 403);

        // Seats available
        EventEnrollment::updateOrCreate(
            ['user_id' => auth()->id()],
            ['event_slot_id' => $slot->id],
        );

        $this->enrolledTo = $slot->id;

        Notification::make()
            ->success()
            ->title('Erfolg')
            ->body('Deine Anmeldung wurde gespeichert.')
            ->send();
    }

    public function deleteEnrollment()
    {
        $enrollment = $this->event->enrollment;

        abort_if($enrollment->slot->start_time->isPast(), 403);

        $enrollment->delete();

        Notification::make()
            ->warning()
            ->title('Erfolg')
            ->body('Deine Anmeldung wurde gelöscht.')
            ->send();

        $this->enrolledTo = null;
    }

    public function render()
    {
        return view('livewire.event-page');
    }
}
