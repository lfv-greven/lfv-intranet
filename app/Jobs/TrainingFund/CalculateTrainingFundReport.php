<?php

namespace App\Jobs\TrainingFund;

use App\Services\TrainingFundReportService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CalculateTrainingFundReport implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $month,
        public bool $overwrite = false,
    ) {}

    public function handle(TrainingFundReportService $service): void
    {
        $month = Carbon::parse($this->month)->startOfMonth();

        Log::info('Training fund report: calculation start', [
            'month' => $month->toDateString(),
            'overwrite' => $this->overwrite,
        ]);

        $service->calculateForMonth($month, $this->overwrite);

        Log::info('Training fund report: calculation completed', [
            'month' => $month->toDateString(),
        ]);
    }
}
