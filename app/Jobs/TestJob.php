<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TestJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Mail::raw('Queue-Test erfolgreich 🚀', function ($message) {
            $message->to('oliver@brunsmann.io')
                ->subject('Laravel Queue Test');
        });
    }
}
