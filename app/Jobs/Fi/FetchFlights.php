<?php

namespace App\Jobs\Fi;

use App\Jobs\Fi\Concerns\HandlesFiSettlementFailure;
use App\Models\FiSettlement;
use App\Models\FiSettlementFlight;
use App\Services\VereinsfliegerClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FetchFlights implements ShouldQueue
{
    use HandlesFiSettlementFailure;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $settlementId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $settlement = FiSettlement::findOrFail($this->settlementId);
        $settings = BuildFiSettlementSettings::normalize($settlement->settings ?? []);

        Log::info('FI settlement: fetch flights start', [
            'settlement_id' => $settlement->id,
            'period_from' => optional($settlement->period_from)->format('Y-m-d'),
            'period_to' => optional($settlement->period_to)->format('Y-m-d'),
            'ftid_filter' => $settings['ftid_filter'],
        ]);

        $client = app(VereinsfliegerClient::class);
        [$success, $status, $response] = $client->callWithRetry(function ($vf) use ($settlement) {
            return $vf->GetFlights_Daterange(
                $settlement->period_from->format('Y-m-d'),
                $settlement->period_to->format('Y-m-d'),
            );
        });

        if (! $success) {
            Log::error('FI settlement: VF flights request failed', [
                'settlement_id' => $settlement->id,
                'http_status' => $status,
                'response' => $response,
            ]);
        }
        $flights = $this->filterFlights(is_array($response) ? $response : [], $settings['ftid_filter']);

        Log::info('FI settlement: flights fetched', [
            'settlement_id' => $settlement->id,
            'raw_count' => is_array($response) ? count($response) : 0,
            'filtered_count' => $flights->count(),
        ]);

        $this->storeFlights($settlement, $flights);

        Log::info('FI settlement: flights stored', [
            'settlement_id' => $settlement->id,
            'stored_count' => $flights->count(),
        ]);
    }

    private function filterFlights(array $response, array $ftidFilter): Collection
    {
        return collect($response)
            ->filter(fn ($row) => is_array($row))
            ->filter(function (array $row) use ($ftidFilter) {
                $ftid = (int) data_get($row, 'ftid');
                $startType = (int) data_get($row, 'starttype');
                $chargeMode = (int) data_get($row, 'chargemode');
                $isCommunityPlane = (int) data_get($row, 'iscommunityplane');

                return in_array($ftid, $ftidFilter, true)
                    && $startType === 1
                    && $chargeMode !== 1
                    && $isCommunityPlane === 1;
            })
            ->values();
    }

    private function storeFlights(FiSettlement $settlement, Collection $flights): void
    {
        $excluded = 0;
        $stored = 0;

        foreach ($flights as $flight) {
            $flightId = (string) data_get($flight, 'flid');

            if ($flightId === '') {
                continue;
            }

            [$fiUid, $fiName] = $this->resolveFi($flight);

            $excludedReason = null;
            if (! $fiUid) {
                $excludedReason = 'missing_fi_uid';
            }

            $flightMinutes = (int) data_get($flight, 'flighttime');
            if ($flightMinutes <= 0) {
                $excludedReason ??= 'invalid_flighttime';
            }

            FiSettlementFlight::updateOrCreate([
                'fi_settlement_id' => $settlement->id,
                'vf_flight_id' => $flightId,
            ], [
                'fi_vf_uid' => $fiUid,
                'fi_name' => $fiName,
                'flight_date' => data_get($flight, 'dateofflight'),
                'departure_time' => data_get($flight, 'departuretime'),
                'arrival_time' => data_get($flight, 'arrivaltime'),
                'flighttime_minutes' => $flightMinutes > 0 ? $flightMinutes : null,
                'blocktime_minutes' => $this->asIntOrNull(data_get($flight, 'blocktime')),
                'planetype' => data_get($flight, 'planetype'),
                'callsign' => data_get($flight, 'callsign'),
                'pilotname' => data_get($flight, 'pilotname'),
                'excluded_reason' => $excludedReason,
                'raw_payload' => $flight,
            ]);

            $stored++;
            if ($excludedReason) {
                $excluded++;
                Log::warning('FI settlement: flight excluded', [
                    'settlement_id' => $settlement->id,
                    'vf_flight_id' => $flightId,
                    'reason' => $excludedReason,
                ]);
            }
        }

        Log::info('FI settlement: store summary', [
            'settlement_id' => $settlement->id,
            'stored' => $stored,
            'excluded' => $excluded,
        ]);
    }

    private function resolveFi(array $flight): array
    {
        $uidAttendant = (int) data_get($flight, 'uidattendant');
        $uidFi = (int) data_get($flight, 'uidfi');

        if ($uidAttendant > 0) {
            return [
                (string) $uidAttendant,
                $this->sanitizeName(data_get($flight, 'attendantname')),
            ];
        }

        if ($uidFi > 0) {
            return [
                (string) $uidFi,
                $this->sanitizeName(data_get($flight, 'finame') ?: data_get($flight, 'attendantname')),
            ];
        }

        return [null, null];
    }

    private function sanitizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $trimmed = trim($name);

        return $trimmed === '' ? null : $trimmed;
    }

    private function asIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }
}
