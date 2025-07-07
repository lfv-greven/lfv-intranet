<?php

namespace App\Console\Commands\Vf;

use App\External\Vereinsflieger;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CheckMotortimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-motortimes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allFlights = cache()->remember('flights', now()->addDay(), function () {
            /**
             * @var Vereinsflieger
             */
            $vf = app()->make('vfadmin');

            $vf->GetFlights_Daterange(
                now()->subWeek()->format('Y-m-d'),
                now()->format('Y-m-d'),
            );

            return $vf->GetResponse();
        });

        $allowedCallsigns = [
            'D-EDDG',
            'D-EJAN',
            'D-EVOK',
            'D-EJHW',
            'D-ESEF',
            'D-ESEG',
            'D-MLFV',
            'D-MHLA',
        ];
        $flights = collect(array_filter($allFlights, fn ($row) => is_array($row)))
            ->filter(fn ($row) => isset($row['motorstart'], $row['motorend']))
            ->whereIn('callsign', $allowedCallsigns)
            ->sortBy('motorstart')
            ->groupBy('callsign')
            ->values();

        foreach ($flights as $flights) {
            $this->info('Checking '.$flights->first()['callsign']);

            $this->checkTimes($flights);

            $this->newLine();
        }
    }

    private function checkTimes(Collection $flights)
    {
        $gaps = [];

        for ($i = 1; $i < $flights->count(); $i++) {
            $previousEnd = $flights[$i - 1]['motorend'];
            $currentStart = $flights[$i]['motorstart'];

            if ($previousEnd !== $currentStart) {
                $gaps[] = [
                    'gap_after_flight' => $i - 1,
                    'expected_start' => $this->formatTime($previousEnd),
                    'actual_start' => $this->formatTime($currentStart),
                    'flight' => $flights[$i],
                ];
            }
        }

        if (empty($gaps)) {
            $this->info('Keine Lücken in den Motorzeiten gefunden.');
        } else {
            $this->warn('Gefundene Lücken in den Motorzeiten:');

            $table = [];

            foreach ($gaps as $gap) {
                $table[] = [
                    data_get($gap, 'flight.flid'),
                    Carbon::parse(data_get($gap, 'flight.dateofflight'))->format('d.m.Y'),
                    data_get($gap, 'flight.pilotname'),
                    data_get($gap, 'flight.attendantname'),
                    $gap['expected_start'],
                    $gap['actual_start'],
                ];
            }
            $this->table(
                ['Flug-ID', 'Datum', 'Pilot', 'Begleiter', 'SOLL-Start', 'IST-Start'],
                $table,
            );
        }
    }

    private function formatTime(string $decimalTime): string
    {
        try {
            [$hours, $minutesFraction] = explode('.', $decimalTime);
            $minutes = round(floatval("0.$minutesFraction") * 60);

            return sprintf('%02d:%02d', $hours, $minutes);
        } catch (\Exception $e) {
            return $decimalTime;
        }
    }
}
