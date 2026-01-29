<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Services\VereinsfliegerUsers;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnrichExpenseWithIban implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Expense $expense)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find respective member
        $member = app(VereinsfliegerUsers::class)->findByMemberId($this->expense->user->memberid);

        if (! $member) {
            return;
        }

        $this->expense->iban = data_get($member, 'iban');
        $this->expense->bic = data_get($member, 'bic');
        $this->expense->saveQuietly();
    }

    public function uniqueId()
    {
        return $this->expense->id;
    }
}
