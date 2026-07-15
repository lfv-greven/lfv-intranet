<?php

namespace App\Jobs\Fi;

use App\Enums\FiSettlementStatus;
use App\Models\FiSettlement;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class BuildFiSettlement implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public FiSettlement $settlement) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->settlement->refresh();

        Log::info('FI settlement: start', [
            'settlement_id' => $this->settlement->id,
            'period_from' => optional($this->settlement->period_from)->format('Y-m-d'),
            'period_to' => optional($this->settlement->period_to)->format('Y-m-d'),
        ]);

        $this->settlement->forceFill([
            'status' => FiSettlementStatus::PROCESSING,
            'started_at' => now(),
            'completed_at' => null,
            'error_message' => null,
        ])->save();

        $settings = BuildFiSettlementSettings::normalize($this->settlement->settings ?? []);
        $this->settlement->forceFill(['settings' => $settings])->save();

        Log::info('FI settlement: dispatch chain', [
            'settlement_id' => $this->settlement->id,
            'settings' => $settings,
        ]);

        Bus::chain([
            new FetchFlights($this->settlement->id),
            new DispatchWorkHoursBatch($this->settlement->id),
        ])->onQueue('vereinsflieger-low')->dispatch();
    }

    public function uniqueId()
    {
        return $this->settlement->id;
    }
}
