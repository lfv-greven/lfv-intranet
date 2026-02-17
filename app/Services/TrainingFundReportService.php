<?php

namespace App\Services;

use App\Enums\TrainingFundReportStatus;
use App\Models\TrainingFundReport;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TrainingFundReportService
{
    private const MOTOR_UL_PLANE_TYPES = [
        'Motorflugzeug',
        'Ultraleicht - Aerodynamisch',
    ];

    private const FTID_FILTER = [2, 8, 12];

    public function calculateForMonth(Carbon $month, bool $overwrite = false): TrainingFundReport
    {
        $month = $month->copy()->startOfMonth();

        $report = TrainingFundReport::firstOrNew([
            'month' => $month->toDateString(),
        ]);

        if ($report->exists && ! $overwrite && $report->status === TrainingFundReportStatus::COMPLETED) {
            return $report;
        }

        $report->fill([
            'status' => TrainingFundReportStatus::PROCESSING,
            'started_at' => now(),
            'completed_at' => null,
            'error_message' => null,
            'motor_ul_minutes' => 0,
            'motor_ul_amount' => 0,
            'winch_starts' => 0,
            'winch_amount' => 0,
            'tow_minutes' => 0,
            'tow_amount' => 0,
            'start_pauschale_quarterly_count' => 0,
            'start_pauschale_monthly_count' => 0,
            'start_pauschale_amount' => 0,
            'total_amount' => 0,
            'source_meta' => null,
        ])->save();

        try {
            $from = $month->copy()->startOfMonth();
            $to = $month->copy()->endOfMonth();

            $flights = $this->fetchFlights($from, $to);
            $baseFiltered = $this->filterFlights($flights);
            $filtered = $this->filterFlightsByFtid($baseFiltered);

            $motorUlMinutes = $filtered
                ->filter(fn (array $flight) => in_array((string) Arr::get($flight, 'planetype'), self::MOTOR_UL_PLANE_TYPES, true))
                ->filter(fn (array $flight) => (int) Arr::get($flight, 'iscommunityplane', 0) === 1)
                ->sum(fn (array $flight) => max(0, (int) Arr::get($flight, 'flighttime', 0)));

            $winchStarts = $filtered
                ->filter(fn (array $flight) => (int) Arr::get($flight, 'starttype') === 5)
                ->filter(fn (array $flight) => (int) Arr::get($flight, 'iscommunityplane', 0) === 1)
                ->filter(fn (array $flight) => count(Arr::get($flight, 'invoiceinfo', [])) > 0)
                ->count();

            $gliderFlights = $filtered
                ->filter(fn (array $flight) => (int) Arr::get($flight, 'starttype') === 3)
                ->filter(fn (array $flight) => (int) Arr::get($flight, 'flidtow', 0) > 0);

            $towFlightsById = $baseFiltered->keyBy(fn (array $flight) => (string) Arr::get($flight, 'flid'));
            $towFlights = $gliderFlights
                ->map(fn (array $flight) => (string) Arr::get($flight, 'flidtow'))
                ->filter()
                ->unique()
                ->map(fn (string $flid) => $towFlightsById->get($flid))
                ->filter();

            $towMinutes = $towFlights
                ->filter(fn (array $flight) => (int) Arr::get($flight, 'starttype') === 1)
                ->filter(fn (array $flight) => (int) Arr::get($flight, 'iscommunityplane', 0) === 1)
                ->sum(fn (array $flight) => max(0, (int) Arr::get($flight, 'flighttime', 0)));

            $motorUlAmount = $this->roundAmount($motorUlMinutes * (5 / 60));
            $winchAmount = $this->roundAmount($winchStarts * 1.5);
            $towAmount = $this->roundAmount($towMinutes * 0.75);
            [$quarterlyCount, $monthlyCount, $startPauschaleAmount, $startPauschaleUnknown] = $this->calculateStartPauschale($from, $to);
            $totalAmount = $this->roundAmount($motorUlAmount + $winchAmount + $towAmount + $startPauschaleAmount);

            $report->fill([
                'status' => TrainingFundReportStatus::COMPLETED,
                'completed_at' => now(),
                'motor_ul_minutes' => $motorUlMinutes,
                'motor_ul_amount' => $motorUlAmount,
                'winch_starts' => $winchStarts,
                'winch_amount' => $winchAmount,
                'tow_minutes' => $towMinutes,
                'tow_amount' => $towAmount,
                'start_pauschale_quarterly_count' => $quarterlyCount,
                'start_pauschale_monthly_count' => $monthlyCount,
                'start_pauschale_amount' => $startPauschaleAmount,
                'total_amount' => $totalAmount,
                'source_meta' => [
                    'date_from' => $from->toDateString(),
                    'date_to' => $to->toDateString(),
                    'flights_total' => count($flights),
                    'flights_filtered' => $filtered->count(),
                    'start_pauschale_unknown' => $startPauschaleUnknown,
                ],
            ])->save();
        } catch (\Throwable $e) {
            $report->fill([
                'status' => TrainingFundReportStatus::FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ])->save();

            throw $e;
        }

        return $report;
    }

    public function queueForMonth(Carbon $month, bool $overwrite = false): TrainingFundReport
    {
        $month = $month->copy()->startOfMonth();

        $report = TrainingFundReport::firstOrNew([
            'month' => $month->toDateString(),
        ]);

        if ($report->exists && ! $overwrite && $report->status === TrainingFundReportStatus::COMPLETED) {
            return $report;
        }

        $report->fill([
            'status' => TrainingFundReportStatus::QUEUED,
            'started_at' => null,
            'completed_at' => null,
            'error_message' => null,
            'motor_ul_minutes' => 0,
            'motor_ul_amount' => 0,
            'winch_starts' => 0,
            'winch_amount' => 0,
            'tow_minutes' => 0,
            'tow_amount' => 0,
            'start_pauschale_quarterly_count' => 0,
            'start_pauschale_monthly_count' => 0,
            'start_pauschale_amount' => 0,
            'total_amount' => 0,
            'source_meta' => null,
        ])->save();

        return $report;
    }

    private function fetchFlights(Carbon $from, Carbon $to): array
    {
        $client = app(VereinsfliegerClient::class);
        [$success, $status, $response] = $client->callWithRetry(function ($vf) use ($from, $to) {
            return $vf->GetFlights_Daterange(
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            );
        });

        if (! $success || ! is_array($response)) {
            throw new \RuntimeException('Vereinsflieger API request failed (HTTP '.$status.').');
        }

        return array_values(array_filter($response, 'is_array'));
    }

    private function filterFlights(array $flights)
    {
        return collect($flights)
            ->filter(fn (array $flight) => (int) Arr::get($flight, 'deleted', 0) === 0)
            ->filter(fn (array $flight) => (int) Arr::get($flight, 'duplicate', 0) === 0)
            ->filter(fn (array $flight) => (int) Arr::get($flight, 'chargemode', 0) !== 1)
            ->values();
    }

    private function filterFlightsByFtid($flights)
    {
        return $flights
            ->filter(fn (array $flight) => in_array((int) Arr::get($flight, 'ftid'), self::FTID_FILTER, true))
            ->values();
    }

    private function calculateStartPauschale(Carbon $from, Carbon $to): array
    {
        $rows = $this->fetchAccountTransactions($from, $to);

        $filtered = collect($rows)
            ->filter(fn (array $row) => (string) Arr::get($row, 'creditaccount') === '52500')
            ->values();

        $quarterly = 0;
        $monthly = 0;
        $amount = 0.0;
        $unknown = 0;

        foreach ($filtered as $row) {
            $bookingText = (string) Arr::get($row, 'bookingtext', '');
            $value = (float) Arr::get($row, 'value', 0);
            $recordType = (string) Arr::get($row, 'recordtype', '');
            $sign = $value < 0 || $recordType === 'SR' ? -1 : 1;

            if (Str::contains($bookingText, '(Q)')) {
                $quarterly++;
                $amount += $sign * 15;

                continue;
            }

            if (Str::contains($bookingText, '(M)')) {
                $monthly++;
                $amount += $sign * 5;

                continue;
            }

            $unknown++;
        }

        return [$quarterly, $monthly, $this->roundAmount($amount), $unknown];
    }

    private function fetchAccountTransactions(Carbon $from, Carbon $to): array
    {
        $client = app(VereinsfliegerClient::class);
        [$success, $status, $response] = $client->callWithRetry(function ($vf) use ($from, $to) {
            return $vf->GetAccountTransactions_daterange(
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            );
        });

        if (! $success || ! is_array($response)) {
            throw new \RuntimeException('Vereinsflieger account request failed (HTTP '.$status.').');
        }

        return array_values(array_filter($response, 'is_array'));
    }

    private function roundAmount(float $amount): float
    {
        return round($amount, 2);
    }
}
