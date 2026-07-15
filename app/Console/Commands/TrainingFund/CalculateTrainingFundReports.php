<?php

namespace App\Console\Commands\TrainingFund;

use App\Jobs\TrainingFund\CalculateTrainingFundReport;
use App\Services\TrainingFundReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateTrainingFundReports extends Command
{
    protected $signature = 'training-fund:calculate {--month=} {--overwrite}';

    protected $description = 'Calculates the training fund report for a month.';

    public function handle(TrainingFundReportService $service): int
    {
        $monthInput = $this->option('month');
        $overwrite = (bool) $this->option('overwrite');

        $month = $this->parseMonth($monthInput);

        $this->info('Queueing training fund report for '.$month->format('Y-m').'...');

        $report = $service->queueForMonth($month, $overwrite);
        CalculateTrainingFundReport::dispatch($report->month->toDateString(), $overwrite);

        $this->info('Queued.');

        return self::SUCCESS;
    }

    private function parseMonth(?string $input): Carbon
    {
        if ($input) {
            return Carbon::createFromFormat('Y-m', $input)->startOfMonth();
        }

        return now()->subMonthNoOverflow()->startOfMonth();
    }
}
