<?php

namespace App\Jobs\TrainingFund;

use App\Jobs\Concerns\UsesVereinsfliegerLowPriorityQueue;
use App\Services\TrainingFundReportService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CalculateTrainingFundReport implements ShouldQueue
{
    use Queueable;
    use UsesVereinsfliegerLowPriorityQueue;

    public function __construct(
        public string $month,
        public bool $overwrite = false,
    ) {
        $this->configureVereinsfliegerLowPriorityQueue();
    }

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
