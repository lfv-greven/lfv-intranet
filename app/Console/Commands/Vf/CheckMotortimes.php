<?php

namespace App\Console\Commands\Vf;

use App\Enums\VereinsfliegerPriority;
use App\Models\MotortimeReminder;
use App\Services\VereinsfliegerClient;
use App\Services\VereinsfliegerUsers;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

class CheckMotortimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-motortimes {--dry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for validation criteria regarding the motor times.';

    private const EDDG = 900;

    /**
     * Execute the console command.
     */
    public function handle(VereinsfliegerClient $client): int
    {
        [$success, $status, $response] = $client->callWithRetry(VereinsfliegerPriority::LOW, function ($vf) {
            return $vf->GetFlights_Daterange(
                now()->subMonth()->format('Y-m-d'),
                now()->format('Y-m-d'),
            );
        });

        if (! $success || ! is_array($response)) {
            $this->error('Vereinsflieger-Flugdaten konnten nicht geladen werden (HTTP '.$status.').');

            return self::FAILURE;
        }

        $allFlights = $response;

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
            $this->info('== Checking '.$flights->first()['callsign'].' ==');

            $this->checkTimes($flights);

            $this->newLine();
        }

        return self::SUCCESS;
    }

    private function checkTimes(Collection $flights)
    {
        $issues = [];

        for ($i = 1; $i < $flights->count(); $i++) {
            $flight = $flights[$i];
            $currentStart = $this->timeToMins($flight['motorstart']);
            $currentEnd = $this->timeToMins($flight['motorend']);
            $flighttime = $flight['flighttime'];

            $previousEnd = $flights[$i - 1]['motorend'];
            $formattedPreviousEnd = $this->formatTime($previousEnd);
            $formattedCurrentStart = $this->formatTime($flight['motorstart']);
            $formattedCurrentEnd = $this->formatTime($flight['motorend']);

            if (floatval($previousEnd) == 0) {
                continue;
            }

            $messages = [];

            // Check previous motorend = current motorstart
            if ($formattedPreviousEnd != $formattedCurrentStart) {
                $messages[] = sprintf(
                    '• Dein Motor-Start muss vom Vorgänger übernommen werden, weicht aber ab. Die Betriebsstunden wurden daher nicht lückenlos erfasst! Solltest du deine Motorzeit korrekt eingetragen haben, sprich dich bitte mit deinem Vorflieger ab und korrigiert den Eintrag gemeinsam. SOLL-Start: %s | IST-Start: %s',
                    $formattedPreviousEnd,
                    $formattedCurrentStart,
                );
            }

            // Check that motor time > 0 mins
            if ($currentStart >= $currentEnd) {
                $messages[] = sprintf(
                    '• Motor-Start (%s) liegt nach Motor-Ende (%s)',
                    $formattedCurrentStart,
                    $formattedCurrentEnd,
                );
            }

            // Check that motortime = flight time
            $motortime = $currentEnd - $currentStart;
            if ($motortime != (int) $flighttime) {
                $messages[] = sprintf(
                    '• Motorzeit (%s Minuten) ist nicht gleich Flugzeit (%s Minuten).',
                    $motortime,
                    (int) $flighttime
                );
            }

            // Check departure rwy
            if ($flight['aiddeparture'] == static::EDDG && blank($flight['runwaydeparture'])) {
                $messages[] = '• Start-Piste nicht eingetragen!';
            }

            // Check destination rwy
            if ($flight['aidarrival'] == static::EDDG && blank($flight['runwayarrival'])) {
                $messages[] = '• Lande-Piste nicht eingetragen!';
            }

            if (filled($messages)) {
                $issues[] = [
                    'message' => implode(PHP_EOL, $messages),
                    'flight' => $flight,
                ];
            }
        }

        if (empty($issues)) {
            $this->info('Keine Fehler in den Motorzeiten gefunden.');
        } else {
            $this->warn('Gefundene Fehler in den Motorzeiten:');

            $table = [];
            $isDry = $this->option('dry') == true;

            foreach ($issues as $issue) {
                $pilotId = data_get($issue, 'flight.uidpilot');
                $attendantId = data_get($issue, 'flight.uidattendant') === '0' ? null : data_get($issue, 'flight.uidattendant');

                $table[] = [
                    data_get($issue, 'flight.flid'),
                    $dof = Carbon::parse(data_get($issue, 'flight.dateofflight'))->format('d.m.Y'),
                    data_get($issue, 'flight.pilotname'),
                    data_get($issue, 'flight.attendantname'),
                    $issue['message'],
                ];

                if ($isDry) {
                    // Skip sending email reminder
                    $this->info('Dry run, skip sending email reminders');

                    continue;
                }

                // Check if reminder already sent
                $flightId = data_get($issue, 'flight.flid');
                if (MotortimeReminder::whereFlightId($flightId)->exists()) {
                    $this->info('Reminder already sent');

                    continue;
                }

                // Collect pilot emails
                $mails = array_values(array_unique(array_filter([
                    $this->getMailForUserId((int) $pilotId),
                    filled($attendantId) ? $this->getMailForUserId((int) $attendantId) : null,
                ], fn ($email) => is_string($email) && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))));

                // No mail addresses exist
                if (blank($mails)) {
                    $this->error('No valid mails found');

                    continue;
                }
                $callsign = data_get($issue, 'flight.callsign');

                Mail::raw(<<<TEXT
Hallo,

wir haben festgestellt, dass dein Flug vom $dof mit $callsign falsch im Vereinsflieger erfasst wurde.

Folgende Fehler wurden festgestellt:
{$issue['message']}

Bitte korrigiere den Flug umgehend sowohl im Vereinsflieger als auch im Boardbuch. Als Pilot bist du für die richtige Dokumentation verantwortlich.

Link zum Flug:
https://vereinsflieger.de/member/community/editflight.php?flid=$flightId

Viele Grüße
Dein LfV-Greven Motorflugteam.
TEXT, function (Message $mail) use ($mails, $flightId) {
                    $mail
                        ->subject("[Dringend] Dein Flug wurde falsch erfasst (#$flightId)")
                        ->priority(Email::PRIORITY_HIGHEST)
                        ->to($mails)
                        ->replyTo('info@sportflugzentrum.de');
                });

                // Log sent mail
                MotortimeReminder::create([
                    'flight_id' => $flightId,
                ]);
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
        } catch (Exception $e) {
            $this->error($e);
            report($e);

            return $decimalTime;
        }
    }

    private function timeToMins(string $decimalTime): int
    {
        try {
            $parts = explode('.', $decimalTime);
            $hours = $parts[0];
            $minutesFraction = $parts[1] ?? '0';
            $minutes = round(floatval("0.$minutesFraction") * 60);

            return (int) $hours * 60 + $minutes;
        } catch (Exception $e) {
            $this->error($e);
            report($e);

            return 0;
        }
    }

    private function getMailForUserId(int $userId): ?string
    {
        $user = app(VereinsfliegerUsers::class)->findByUserId($userId, VereinsfliegerPriority::LOW);
        $email = data_get($user, 'email');

        if (! is_string($email)) {
            return null;
        }

        $email = trim($email);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
