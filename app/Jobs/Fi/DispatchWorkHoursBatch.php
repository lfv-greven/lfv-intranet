<?php

namespace App\Jobs\Fi;

use App\Jobs\Fi\Concerns\HandlesFiSettlementFailure;
use App\Models\FiSettlement;
use App\Models\FiSettlementFlight;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class DispatchWorkHoursBatch implements ShouldQueue
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
        Log::info('FI settlement: dispatch workhours batch', [
            'settlement_id' => $settlement->id,
        ]);

        $flights = FiSettlementFlight::query()
            ->where('fi_settlement_id', $settlement->id)
            ->whereNull('excluded_reason')
            ->whereNotNull('fi_vf_uid')
            ->whereNull('workhour_sent_at')
            ->orderBy('flight_date')
            ->orderBy('vf_flight_id')
            ->get(['id']);

        if ($flights->isEmpty()) {
            Log::info('FI settlement: no workhours to send', [
                'settlement_id' => $settlement->id,
            ]);
            FinalizeSettlement::dispatch($settlement->id);

            return;
        }

        $jobs = $flights
            ->map(fn ($flight) => new SendWorkHourForFlight($settlement->id, $flight->id))
            ->all();

        Bus::batch($jobs)
            ->name('FI-Workhours '.$settlement->id)
            ->then(function (Batch $batch) use ($settlement) {
                Log::info('FI settlement: workhours batch completed', [
                    'settlement_id' => $settlement->id,
                    'batch_id' => $batch->id,
                    'total_jobs' => $batch->totalJobs,
                    'failed_jobs' => $batch->failedJobs,
                ]);

                if ($batch->failedJobs === 0) {
                    FinalizeSettlement::dispatch($settlement->id);
                }
            })
            ->catch(function (Batch $batch, \Throwable $exception) use ($settlement) {
                Log::error('FI settlement: workhours batch failed', [
                    'settlement_id' => $settlement->id,
                    'batch_id' => $batch->id,
                    'message' => $exception->getMessage(),
                ]);
            })
            ->dispatch();

        Log::info('FI settlement: workhours batch dispatched', [
            'settlement_id' => $settlement->id,
            'batch_jobs' => count($jobs),
        ]);
    }
}
