<?php

namespace App\Jobs\Vf;

use App\Models\Refueling;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendRefueling implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Refueling $refueling) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $success = $this->refueling->vfExport();

        if (! $success) {
            $this->release(60);
        }
    }

    public function uniqueId(): string
    {
        return $this->refueling->id;
    }
}
