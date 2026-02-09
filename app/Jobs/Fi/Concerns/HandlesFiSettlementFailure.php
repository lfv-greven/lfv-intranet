<?php

namespace App\Jobs\Fi\Concerns;

use App\Enums\FiSettlementStatus;
use App\Models\FiSettlement;
use Illuminate\Support\Facades\Log;
use Throwable;

trait HandlesFiSettlementFailure
{
    public function failed(Throwable $exception): void
    {
        Log::error('FI settlement: job failed', [
            'settlement_id' => $this->settlementId ?? null,
            'job' => static::class,
            'message' => $exception->getMessage(),
        ]);

        report($exception);

        $settlementId = $this->settlementId ?? null;

        if (! $settlementId) {
            return;
        }

        $settlement = FiSettlement::find($settlementId);

        if (! $settlement) {
            return;
        }

        $settlement->forceFill([
            'status' => FiSettlementStatus::FAILED,
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ])->save();
    }
}
