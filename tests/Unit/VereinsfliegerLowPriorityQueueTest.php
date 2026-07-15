<?php

namespace Tests\Unit;

use App\Jobs\EnrichExpenseWithIban;
use App\Jobs\Fi\FetchFlights;
use App\Jobs\Fi\SendWorkHourForFlight;
use App\Jobs\Middleware\ReleaseVereinsfliegerDeferred;
use App\Jobs\TrainingFund\CalculateTrainingFundReport;
use App\Jobs\Vf\SendRefueling;
use App\Models\Expense;
use App\Models\Refueling;
use Tests\TestCase;

class VereinsfliegerLowPriorityQueueTest extends TestCase
{
    public function test_all_vereinsflieger_jobs_use_the_shared_low_priority_policy(): void
    {
        $jobs = [
            new SendRefueling(new Refueling),
            new FetchFlights('settlement-id'),
            new SendWorkHourForFlight('settlement-id', 'flight-id'),
            new CalculateTrainingFundReport('2026-07-01'),
            new EnrichExpenseWithIban(new Expense),
        ];

        foreach ($jobs as $job) {
            $this->assertSame('vereinsflieger-low', $job->queue);
            $this->assertSame(10, $job->tries);
            $this->assertInstanceOf(ReleaseVereinsfliegerDeferred::class, $job->middleware()[0]);
        }
    }
}
