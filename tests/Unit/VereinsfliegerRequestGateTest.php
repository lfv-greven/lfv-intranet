<?php

namespace Tests\Unit;

use App\Enums\VereinsfliegerPriority;
use App\Exceptions\VereinsfliegerDeferred;
use App\Services\VereinsfliegerRequestGate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class VereinsfliegerRequestGateTest extends TestCase
{
    private VereinsfliegerRequestGate $gate;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.vereinsflieger.cid', 'test-cid');
        config()->set('services.vereinsflieger.key', 'test-key');
        config()->set('services.vereinsflieger.rate_limit', [
            'cache_store' => 'array',
            'namespace' => 'vereinsflieger-test',
            'unauthenticated_interval_ms' => 3750,
            'authenticated_interval_ms' => 1250,
            'low_authenticated_interval_ms' => 2000,
            'high_max_wait_ms' => 0,
            'low_max_wait_ms' => 0,
            'login_failure_limit' => 8,
            'login_failure_window_seconds' => 180,
            'lock_seconds' => 90,
            'cooldown_seconds' => 180,
        ]);

        Cache::store('array')->flush();
        Carbon::setTestNow(Carbon::parse('2026-07-14 10:00:00.000'));
        $this->gate = app(VereinsfliegerRequestGate::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_high_priority_request_can_use_the_next_global_slot_before_low_priority(): void
    {
        $this->gate->admit(VereinsfliegerPriority::LOW, false);

        Carbon::setTestNow(now()->addMilliseconds(1250));
        $this->gate->admit(VereinsfliegerPriority::HIGH, false);

        try {
            $this->gate->admit(VereinsfliegerPriority::LOW, false);
            $this->fail('The low-priority request should be deferred.');
        } catch (VereinsfliegerDeferred $exception) {
            $this->assertSame('local_rate_limit', $exception->reason);
        }
    }

    public function test_global_cooldown_defers_every_priority(): void
    {
        $this->gate->cooldown(30);

        try {
            $this->gate->admit(VereinsfliegerPriority::HIGH, false);
            $this->fail('The request should be deferred during the cooldown.');
        } catch (VereinsfliegerDeferred $exception) {
            $this->assertSame('local_rate_limit', $exception->reason);
            $this->assertGreaterThanOrEqual(29, $exception->retryAfter);
        }
    }

    public function test_credential_failures_are_persisted_when_login_throws(): void
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            try {
                $this->gate->withinLoginAttempt(function (callable $recordCredentialFailure): void {
                    $recordCredentialFailure();

                    throw new RuntimeException('Login failed after the credential was rejected.');
                });
            } catch (RuntimeException) {
                // The failed attempt still has to count towards the shared safety limit.
            }
        }

        try {
            $this->gate->withinLoginAttempt(fn () => null);
            $this->fail('The ninth attempt should be deferred.');
        } catch (VereinsfliegerDeferred $exception) {
            $this->assertSame('login_failures', $exception->reason);
            $this->assertGreaterThanOrEqual(179, $exception->retryAfter);
        }
    }
}
