<?php

namespace App\Jobs\Fi;

use App\Enums\FiSettlementStatus;
use App\Jobs\Fi\Concerns\HandlesFiSettlementFailure;
use App\Models\FiSettlement;
use App\Models\FiSettlementFlight;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class RetryRejectedWorkHours implements ShouldBeUnique, ShouldQueue
{
    use HandlesFiSettlementFailure;
    use Queueable;

    public function __construct(public string $settlementId) {}

    public function handle(): void
    {
        $started = FiSettlement::query()
            ->whereKey($this->settlementId)
            ->where('status', FiSettlementStatus::QUEUED->value)
            ->update([
                'status' => FiSettlementStatus::PROCESSING->value,
                'started_at' => now(),
                'completed_at' => null,
                'error_message' => null,
                'updated_at' => now(),
            ]) === 1;

        if (! $started) {
            Log::info('FI settlement: skip retry rejected workhours', [
                'settlement_id' => $this->settlementId,
            ]);

            return;
        }

        $settlement = FiSettlement::findOrFail($this->settlementId);

        $flightIds = $settlement->retryableRejectedWorkHours()
            ->orderBy('flight_date')
            ->orderBy('vf_flight_id')
            ->pluck('id');

        Log::info('FI settlement: retry rejected workhours start', [
            'settlement_id' => $settlement->id,
            'flight_count' => $flightIds->count(),
        ]);

        if ($flightIds->isEmpty()) {
            FinalizeSettlement::dispatch($settlement->id);

            return;
        }

        FiSettlementFlight::query()
            ->whereKey($flightIds)
            ->update([
                'excluded_reason' => null,
                'updated_at' => now(),
            ]);

        $jobs = $flightIds
            ->map(fn (string $flightId) => new SendWorkHourForFlight($settlement->id, $flightId))
            ->all();

        Bus::batch($jobs)
            ->name('FI-Retry-Workhours '.$settlement->id)
            ->then(function (Batch $batch) use ($settlement) {
                Log::info('FI settlement: retry workhours batch completed', [
                    'settlement_id' => $settlement->id,
                    'batch_id' => $batch->id,
                    'total_jobs' => $batch->totalJobs,
                    'failed_jobs' => $batch->failedJobs,
                ]);

                if ($batch->failedJobs === 0) {
                    FinalizeSettlement::dispatch($settlement->id);
                }
            })
            ->catch(function (Batch $batch, Throwable $exception) use ($settlement) {
                Log::error('FI settlement: retry workhours batch failed', [
                    'settlement_id' => $settlement->id,
                    'batch_id' => $batch->id,
                    'message' => $exception->getMessage(),
                ]);
            })
            ->dispatch();

        Log::info('FI settlement: retry workhours batch dispatched', [
            'settlement_id' => $settlement->id,
            'batch_jobs' => count($jobs),
        ]);
    }

    public function uniqueId(): string
    {
        return $this->settlementId;
    }
}
