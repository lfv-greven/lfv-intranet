<?php

namespace Tests\Feature;

use App\Enums\FiSettlementStatus;
use App\Jobs\Fi\RetryRejectedWorkHours;
use App\Jobs\Fi\SendWorkHourForFlight;
use App\Models\FiSettlement;
use App\Models\FiSettlementFlight;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class FiSettlementRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_job_only_requeues_vf_rejected_unsent_workhours(): void
    {
        Bus::fake();

        $settlement = $this->createSettlement([
            'status' => FiSettlementStatus::QUEUED,
        ]);

        $retryableA = $this->createFlight($settlement, [
            'vf_flight_id' => 'vf-1',
            'excluded_reason' => 'vf_rejected',
            'flight_date' => '2026-02-03',
        ]);
        $retryableB = $this->createFlight($settlement, [
            'vf_flight_id' => 'vf-2',
            'excluded_reason' => 'vf_rejected',
            'flight_date' => '2026-02-04',
        ]);
        $this->createFlight($settlement, [
            'vf_flight_id' => 'vf-3',
            'excluded_reason' => 'missing_fi_uid',
        ]);
        $sentRejected = $this->createFlight($settlement, [
            'vf_flight_id' => 'vf-4',
            'excluded_reason' => 'vf_rejected',
            'workhour_sent_at' => now(),
        ]);

        (new RetryRejectedWorkHours($settlement->id))->handle();

        $this->assertSame(FiSettlementStatus::PROCESSING, $settlement->refresh()->status);
        $this->assertNull($retryableA->refresh()->excluded_reason);
        $this->assertNull($retryableB->refresh()->excluded_reason);
        $this->assertSame('missing_fi_uid', FiSettlementFlight::where('vf_flight_id', 'vf-3')->firstOrFail()->excluded_reason);
        $this->assertSame('vf_rejected', $sentRejected->refresh()->excluded_reason);

        $expectedFlightIds = collect([$retryableA->id, $retryableB->id])->sort()->values()->all();

        Bus::assertBatchCount(1);
        Bus::assertBatched(function (PendingBatch $batch) use ($settlement, $expectedFlightIds): bool {
            $actualFlightIds = $batch->jobs
                ->map(fn ($job) => $job instanceof SendWorkHourForFlight ? $job->flightId : null)
                ->filter()
                ->sort()
                ->values()
                ->all();

            return $batch->name === 'FI-Retry-Workhours '.$settlement->id
                && $actualFlightIds === $expectedFlightIds;
        });
    }

    public function test_retry_queue_guard_requires_retryable_workhours_and_non_running_status(): void
    {
        $settlement = $this->createSettlement([
            'status' => FiSettlementStatus::COMPLETED,
            'completed_at' => now(),
            'error_message' => 'Previous error',
        ]);
        $this->createFlight($settlement, [
            'excluded_reason' => 'vf_rejected',
        ]);

        $this->assertTrue($settlement->queueRejectedWorkHourRetry());
        $settlement->refresh();
        $this->assertSame(FiSettlementStatus::QUEUED, $settlement->status);
        $this->assertNull($settlement->completed_at);
        $this->assertNull($settlement->error_message);

        $this->assertFalse($settlement->queueRejectedWorkHourRetry());

        $withoutRejectedWorkHours = $this->createSettlement([
            'status' => FiSettlementStatus::COMPLETED,
        ]);
        $this->createFlight($withoutRejectedWorkHours, [
            'excluded_reason' => 'missing_fi_uid',
        ]);

        $this->assertFalse($withoutRejectedWorkHours->queueRejectedWorkHourRetry());
    }

    public function test_send_workhour_marks_later_duplicates_as_already_sent(): void
    {
        $previousSettlement = $this->createSettlement();
        $this->createFlight($previousSettlement, [
            'vf_flight_id' => 'vf-duplicate',
            'vf_workhour_id' => 'wh-1',
            'workhour_sent_at' => now(),
        ]);

        $settlement = $this->createSettlement([
            'status' => FiSettlementStatus::PROCESSING,
        ]);
        $flight = $this->createFlight($settlement, [
            'vf_flight_id' => 'vf-duplicate',
            'excluded_reason' => null,
        ]);

        (new SendWorkHourForFlight($settlement->id, $flight->id))->handle();

        $this->assertSame('already_sent', $flight->refresh()->excluded_reason);
        $this->assertNull($flight->workhour_sent_at);
    }

    private function createSettlement(array $attributes = []): FiSettlement
    {
        return FiSettlement::create(array_merge([
            'period_from' => '2026-02-01',
            'period_to' => '2026-02-28',
            'status' => FiSettlementStatus::COMPLETED,
            'settings' => ['ftid_filter' => [2, 8, 11, 12]],
        ], $attributes));
    }

    private function createFlight(FiSettlement $settlement, array $attributes = []): FiSettlementFlight
    {
        return FiSettlementFlight::create(array_merge([
            'fi_settlement_id' => $settlement->id,
            'vf_flight_id' => 'vf-'.str()->ulid(),
            'fi_vf_uid' => '123',
            'fi_name' => 'FI Name',
            'flight_date' => '2026-02-02',
            'departure_time' => '10:00:00',
            'arrival_time' => '10:30:00',
            'flighttime_minutes' => 30,
            'blocktime_minutes' => 35,
            'planetype' => 'ASK 21',
            'callsign' => 'D-0000',
            'pilotname' => 'Pilot Name',
            'excluded_reason' => null,
            'raw_payload' => [
                'flid' => 'vf-'.str()->ulid(),
                'departurelocation' => 'EDLG',
                'arrivallocation' => 'EDLG',
            ],
        ], $attributes));
    }
}
