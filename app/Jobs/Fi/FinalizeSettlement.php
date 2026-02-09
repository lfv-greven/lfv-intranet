<?php

namespace App\Jobs\Fi;

use App\Enums\FiSettlementStatus;
use App\Jobs\Fi\Concerns\HandlesFiSettlementFailure;
use App\Models\FiSettlement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FinalizeSettlement implements ShouldQueue
{
    use HandlesFiSettlementFailure;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $settlementId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $settlement = FiSettlement::findOrFail($this->settlementId);

        $settlement->forceFill([
            'status' => FiSettlementStatus::COMPLETED,
            'completed_at' => now(),
        ])->save();

        Log::info('FI settlement: completed', [
            'settlement_id' => $settlement->id,
            'completed_at' => optional($settlement->completed_at)->toDateTimeString(),
        ]);
    }
}
