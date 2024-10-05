<?php

namespace App\Jobs;

use App\Models\Expense;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendExpenseToAccounting implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Expense $expense) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::raw(sprintf(
            "Erstattung ID: %s\nFür: %s",
            $this->expense->id,
            $this->expense->user->name,
        ), function (Message $message) {
            $message->subject('Erstattung');
            $message->to('belege@sportflugzentrum.de');
            $message->attachData(Storage::get($this->expense->filename), basename($this->expense->filename));
        });
    }

    public function uniqueId()
    {
        return $this->expense->id;
    }
}
