<?php

namespace App\Listeners\Expense;

use App\Events\ExpenseCreated;
use App\Notifications\Expense\ExpenseAcknowledgement;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUserAboutAcknowledgement implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ExpenseCreated $event): void
    {
        $event->expense->user->notify(new ExpenseAcknowledgement($event->expense));
    }
}
