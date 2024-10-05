<?php

namespace App\Jobs;

use App\Models\Expense;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;

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
        $memberList = cache()->remember('vf:users', 60 * 60 * 24, function () {
            $vf = app()->make('vfadmin');
            $vf->GetUsers();

            return $vf->GetResponse();
        });

        // Find respective member
        $member = Arr::first($memberList, fn ($m) => $m['memberid'] == $this->expense->user->memberid);

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
