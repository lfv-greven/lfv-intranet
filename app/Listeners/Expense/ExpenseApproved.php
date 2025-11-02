<?php

namespace App\Listeners\Expense;

use App\Enums\ExpenseStatus;
use App\Events\ExpenseUpdated;
use App\Jobs\SendExpenseToAccounting;
use App\Notifications\Expense\NotifyUserAboutApproval;

class ExpenseApproved
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ExpenseUpdated $event): void
    {
        // Check if change was an approval
        if ($event->expense->isDirty('status') && $event->expense->status == ExpenseStatus::APPROVED) {
            // Notify user
            $event->expense->user->notify(new NotifyUserAboutApproval($event->expense));

            // Send to internal accounting
            SendExpenseToAccounting::dispatch($event->expense);
        }
    }
}
