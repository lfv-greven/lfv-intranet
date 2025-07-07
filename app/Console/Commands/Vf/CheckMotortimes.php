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
        $issues = [];

        for ($i = 1; $i < $flights->count(); $i++) {
            $previousEnd = $flights[$i - 1]['motorend'];
            $currentStart = $flights[$i]['motorstart'];
            $currentEnd = $flights[$i]['motorend'];
            $flighttime = $flights[$i]['flighttime'];

            $messages = [];

            // Check previous motorend = current motorstart
            if ($previousEnd !== $currentStart) {
                $messages[] = sprintf(
                    'Motor-Start entspricht nicht Motor-Ende des vorherigen Fluges. Flug nicht lückenlos erfasst! SOLL-Start: %s | IST-Start: %s',
                    $this->formatTime($previousEnd),
                    $this->formatTime($currentStart),
                );
            }

            // Check that motor time > 0 mins
            if ($currentStart >= $currentEnd) {
                $messages[] = sprintf(
                    'Motor-Start (%s) liegt nach Motor-Ende (%s)',
                    $this->formatTime($currentStart),
                    $this->formatTime($currentEnd),
                );
            }

            // Check that motortime = flight time
            $motortime = $currentEnd - $currentStart;
            if ($this->timeToMins($motortime) != (int) $flighttime) {
                $messages[] = sprintf(
                    'Motorzeit (%s Minuten) ist nicht gleich Flugzeug (%s Minuten).',
                    $this->timeToMins($motortime),
                    (int) $flighttime
                );
            }

            if (filled($messages)) {
                $issues[] = [
                    'message' => implode(PHP_EOL, $messages),
                    'flight' => $flights[$i],
                ];
            }
        }

        if (empty($issues)) {
            $this->info('Keine Fehler in den Motorzeiten gefunden.');
        } else {
            $this->warn('Gefundene Fehler in den Motorzeiten:');

            $table = [];

            foreach ($issues as $issue) {
                $table[] = [
                    data_get($issue, 'flight.flid'),
                    Carbon::parse(data_get($issue, 'flight.dateofflight'))->format('d.m.Y'),
                    data_get($issue, 'flight.pilotname'),
                    data_get($issue, 'flight.attendantname'),
                    $issue['message'],
                ];
            }
            $this->table(
                ['Flug-ID', 'Datum', 'Pilot', 'Begleiter', 'Fehler'],
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

    private function timeToMins(string $decimalTime): int
    {
        try {
            [$hours, $minutesFraction] = explode('.', $decimalTime);
            $minutes = round(floatval("0.$minutesFraction") * 60);

            return (int) $hours * 60 + $minutes;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
