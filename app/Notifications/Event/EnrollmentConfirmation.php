<?php

namespace App\Notifications\Event;

use App\Models\EventEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentConfirmation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public EventEnrollment $eventEnrollment)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->name;
        $eventName = $this->eventEnrollment->event->title;
        $startTime = $this->eventEnrollment->slot->start_time->format('d.m.Y H:i');
        return (new MailMessage)
            ->subject("Anmeldebestätigung: $eventName")
            ->greeting("Hallo $name,")
            ->line("Du hast dich soeben für „{$eventName}“ ab $startTime Uhr angemeldet. Um deine Anmeldung zu bearbeiten, klicke einfach auf den nachfolgenden Link.")
            ->action('Anmeldung bearbeiten', route('event', ['event' => $this->eventEnrollment->event->id]))
            ->salutation('Vielen Dank!');
    }

}
