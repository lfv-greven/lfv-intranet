<?php

namespace App\Jobs\Fi;

use App\Jobs\Fi\Concerns\HandlesFiSettlementFailure;
use App\Models\FiSettlementFlight;
use App\Services\VereinsfliegerClient;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendWorkHourForFlight implements ShouldQueue
{
    use Batchable;
    use HandlesFiSettlementFailure;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $settlementId, public string $flightId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $flight = FiSettlementFlight::query()
            ->where('fi_settlement_id', $this->settlementId)
            ->where('id', $this->flightId)
            ->first();

        if (! $flight || $flight->excluded_reason) {
            Log::info('FI settlement: skip workhour (missing/excluded)', [
                'settlement_id' => $this->settlementId,
                'flight_id' => $this->flightId,
            ]);

            return;
        }

        if ($flight->workhour_sent_at) {
            Log::info('FI settlement: skip workhour (already sent)', [
                'settlement_id' => $this->settlementId,
                'flight_id' => $flight->id,
                'vf_workhour_id' => $flight->vf_workhour_id,
            ]);

            return;
        }

        $categoryId = (int) config('fi_workhours.category_id', 0);
        if ($categoryId <= 0) {
            $flight->forceFill(['excluded_reason' => 'missing_workhour_category'])->save();
            Log::warning('FI settlement: missing workhour category', [
                'settlement_id' => $this->settlementId,
                'flight_id' => $flight->id,
            ]);

            return;
        }

        $hours = $this->formatMinutesToHours($flight->flighttime_minutes);
        if ($hours === null) {
            $flight->forceFill(['excluded_reason' => 'invalid_flighttime'])->save();
            Log::warning('FI settlement: invalid flighttime', [
                'settlement_id' => $this->settlementId,
                'flight_id' => $flight->id,
                'flighttime_minutes' => $flight->flighttime_minutes,
            ]);

            return;
        }

        $payload = [
            'uid' => (int) $flight->fi_vf_uid,
            'jobdate' => optional($flight->flight_date)->format('Y-m-d'),
            'jobtext' => $this->jobText($flight),
            'hours' => $hours,
            'category' => $categoryId,
            'status' => 2,
        ];

        $timeFrom = $this->normalizeTime($flight->departure_time);
        if ($timeFrom) {
            $payload['timefrom'] = $timeFrom;
        }

        $timeTo = $this->normalizeTime($flight->arrival_time);
        if ($timeTo) {
            $payload['timeto'] = $timeTo;
        }

        $payload['comment'] = $this->comment($flight);

        $client = app(VereinsfliegerClient::class);
        [$success, $status, $response] = $client->callWithRetry(function ($vf) use ($payload) {
            return $vf->InsertWorkHours($payload);
        });

        if (! $success) {
            $flight->forceFill(['excluded_reason' => 'vf_rejected'])->save();
            Log::error('FI settlement: VF rejected workhour', [
                'settlement_id' => $this->settlementId,
                'flight_id' => $flight->id,
                'http_status' => $status,
                'response' => $response,
            ]);

            return;
        }

        $workhourId = $this->extractWorkhourId($response);

        $flight->forceFill([
            'vf_workhour_id' => $workhourId,
            'workhour_sent_at' => now(),
        ])->save();

        Log::info('FI settlement: workhour sent', [
            'settlement_id' => $this->settlementId,
            'flight_id' => $flight->id,
            'vf_flight_id' => $flight->vf_flight_id,
            'vf_workhour_id' => $workhourId,
            'uid' => $flight->fi_vf_uid,
        ]);
    }

    private function formatMinutesToHours(?int $minutes): ?string
    {
        if (! $minutes || $minutes <= 0) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    private function normalizeTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (! preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
            return null;
        }

        return substr($value, 0, 5);
    }

    private function jobText(FiSettlementFlight $flight): string
    {
        $payload = $flight->raw_payload ?? [];
        $uidAttendant = (int) data_get($payload, 'uidattendant');
        $uidFi = (int) data_get($payload, 'uidfi');

        $label = $uidFi > 0 && $uidAttendant === 0
            ? 'Fluggenehmigung für'
            : 'Fluglehrer für';

        $name = $flight->pilotname ?: $flight->fi_name ?: 'Unbekannt';
        $callsign = $flight->callsign ?: 'unbekanntes Kennzeichen';

        return sprintf('%s %s auf %s', $label, $name, $callsign);
    }

    private function comment(FiSettlementFlight $flight): string
    {
        $payload = $flight->raw_payload ?? [];
        $departure = data_get($payload, 'departurelocation');
        $destination = data_get($payload, 'arrivallocation');

        if (filled($departure) && filled($destination)) {
            return Str::limit(sprintf('%s – %s', $departure, $destination), 120, '');
        }

        if (filled($departure)) {
            return Str::limit((string) $departure, 120, '');
        }

        return Str::limit((string) $destination, 120, '');
    }

    private function extractWorkhourId(mixed $response): ?string
    {
        if (is_array($response)) {
            foreach (['whid', 'id', 'wid', 'workhourid'] as $key) {
                if (array_key_exists($key, $response)) {
                    return (string) $response[$key];
                }
            }
        }

        return null;
    }
}
