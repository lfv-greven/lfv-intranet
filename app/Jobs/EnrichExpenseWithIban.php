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
        $bankData = app(VereinsfliegerUsers::class)->findBankDataByMemberId($this->expense->user->memberid);

        if (! $bankData) {
            return;
        }

        $this->expense->iban = iban_to_machine_format(data_get($bankData, 'iban'));
        $this->expense->bic = data_get($bankData, 'bic');
        $this->expense->saveQuietly();
    }

    public function uniqueId()
    {
        return $this->expense->id;
    }
}
