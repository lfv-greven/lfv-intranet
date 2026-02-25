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

        $this->dispatch('umami-track', name: 'event_page_viewed', data: [
            'has_existing_enrollment' => filled($this->enrolledTo),
        ]);
    }

    public function updatedEnrolledTo($id)
    {
        $this->dispatch('umami-track', name: 'event_enrollment_attempt');

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

        $this->dispatch('umami-track', name: 'event_enrollment_success', data: [
            'has_selection' => true,
        ]);
    }

    public function deleteEnrollment()
    {
        $this->dispatch('umami-track', name: 'event_enrollment_delete_attempt');

        $enrollment = $this->event->enrollment;

        abort_if($enrollment->slot->start_time->isPast(), 403);

        $enrollment->delete();

        Notification::make()
            ->warning()
            ->title('Erfolg')
            ->body('Deine Anmeldung wurde gelöscht.')
            ->send();

        $this->enrolledTo = null;

        $this->dispatch('umami-track', name: 'event_enrollment_delete_success');
    }

    public function render()
    {
        return view('livewire.event-page');
    }
}
