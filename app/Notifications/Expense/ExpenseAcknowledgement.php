<?php

namespace App\Notifications\Expense;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseAcknowledgement extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Expense $expense)
    {
        //
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
        return (new MailMessage)
            ->subject('Eingangsbestätigung')
            ->greeting('Hey '.$notifiable->name.',')
            ->line(sprintf(
                'Dein Beleg für %s ist eingegangen und wird schnellstmöglich bearbeitet.',
                $this->expense->reason,
            ));
    }
}
