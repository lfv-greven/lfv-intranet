<?php

namespace App\Console\Commands\TrainingFund;

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

        $this->info('Calculating training fund report for '.$month->format('Y-m').'...');

        $service->calculateForMonth($month, $overwrite);

        $this->info('Done.');

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
