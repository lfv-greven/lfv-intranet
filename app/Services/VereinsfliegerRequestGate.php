<?php

namespace App\Services;

use App\Enums\VereinsfliegerPriority;
use App\Exceptions\VereinsfliegerDeferred;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;

class VereinsfliegerRequestGate
{
    public function __construct(private readonly Repository $cache) {}

    public function admit(VereinsfliegerPriority $priority, bool $unauthenticated): void
    {
        $maxWaitMilliseconds = (int) config(
            'services.vereinsflieger.rate_limit.'.($priority === VereinsfliegerPriority::HIGH ? 'high_max_wait_ms' : 'low_max_wait_ms'),
            4000,
        );
        $startedAt = $this->nowMilliseconds();

        while (true) {
            $waitMilliseconds = $this->reserve($priority, $unauthenticated);

            if ($waitMilliseconds === 0) {
                return;
            }

            if (($this->nowMilliseconds() - $startedAt) + $waitMilliseconds > $maxWaitMilliseconds) {
                throw new VereinsfliegerDeferred(
                    $this->secondsFromMilliseconds($waitMilliseconds),
                    'local_rate_limit',
                );
            }

            usleep($waitMilliseconds * 1000);
        }
    }

    public function cooldown(?int $retryAfter = null): int
    {
        $seconds = max(1, $retryAfter ?? (int) config('services.vereinsflieger.rate_limit.cooldown_seconds', 180));
        $lock = $this->cache->lock($this->key('admission-lock'), $this->lockSeconds());

        try {
            return $lock->block(2, function () use ($seconds): int {
                $this->storeCooldown($seconds);

                return $seconds;
            });
        } catch (LockTimeoutException) {
            // Record the cooldown even if the short admission lock wait times out.
            $this->storeCooldown($seconds);

            return $seconds;
        }
    }

    public function lockSeconds(): int
    {
        return max(90, (int) config('services.vereinsflieger.rate_limit.lock_seconds', 90));
    }

    public function withinLoginAttempt(Closure $callback): mixed
    {
        $lock = $this->cache->lock($this->key('login-failures-lock'), $this->lockSeconds());

        if (! $lock->get()) {
            throw new VereinsfliegerDeferred(1, 'login_lock');
        }

        $failures = [];

        try {
            $now = $this->nowMilliseconds();
            $windowMilliseconds = (int) config('services.vereinsflieger.rate_limit.login_failure_window_seconds', 180) * 1000;
            $failures = collect($this->cache->get($this->key('login-failures'), []))
                ->filter(fn (mixed $timestamp): bool => is_int($timestamp) && $timestamp > $now - $windowMilliseconds)
                ->values()
                ->all();

            $limit = (int) config('services.vereinsflieger.rate_limit.login_failure_limit', 8);
            if (count($failures) >= $limit) {
                $retryAt = min($failures) + $windowMilliseconds;

                throw new VereinsfliegerDeferred(
                    $this->secondsFromMilliseconds($retryAt - $now),
                    'login_failures',
                );
            }

            $recordCredentialFailure = function () use (&$failures): void {
                $failures[] = $this->nowMilliseconds();
            };

            return $callback($recordCredentialFailure);
        } finally {
            if ($failures !== []) {
                $this->cache->put(
                    $this->key('login-failures'),
                    $failures,
                    now()->addSeconds((int) config('services.vereinsflieger.rate_limit.login_failure_window_seconds', 180)),
                );
            }

            $lock->release();
        }
    }

    public function key(string $suffix): string
    {
        $namespace = (string) config('services.vereinsflieger.rate_limit.namespace', 'vereinsflieger');
        $identity = hash('sha256', implode('|', [
            (string) config('services.vereinsflieger.cid'),
            (string) config('services.vereinsflieger.key'),
        ]));

        return $namespace.':'.$identity.':'.$suffix;
    }

    private function reserve(VereinsfliegerPriority $priority, bool $unauthenticated): int
    {
        $lock = $this->cache->lock($this->key('admission-lock'), $this->lockSeconds());

        if (! $lock->get()) {
            return 1000;
        }

        try {
            $now = $this->nowMilliseconds();
            $cooldownUntil = (int) $this->cache->get($this->key('cooldown-until'), 0);
            if ($cooldownUntil > $now) {
                return $cooldownUntil - $now;
            }

            $scope = $unauthenticated ? 'unauthenticated' : 'authenticated';
            $globalKey = $this->key('next-'.$scope);
            $globalNext = (int) $this->cache->get($globalKey, 0);
            $lowNext = 0;

            if (! $unauthenticated && $priority === VereinsfliegerPriority::LOW) {
                $lowNext = (int) $this->cache->get($this->key('next-low-authenticated'), 0);
            }

            $nextAllowedAt = max($globalNext, $lowNext);
            if ($nextAllowedAt > $now) {
                return $nextAllowedAt - $now;
            }

            $intervalMilliseconds = (int) config('services.vereinsflieger.rate_limit.'.$scope.'_interval_ms');
            $this->cache->put($globalKey, $now + $intervalMilliseconds, now()->addMinutes(5));

            if (! $unauthenticated && $priority === VereinsfliegerPriority::LOW) {
                $lowIntervalMilliseconds = (int) config('services.vereinsflieger.rate_limit.low_authenticated_interval_ms');
                $this->cache->put(
                    $this->key('next-low-authenticated'),
                    $now + $lowIntervalMilliseconds,
                    now()->addMinutes(5),
                );
            }

            return 0;
        } finally {
            $lock->release();
        }
    }

    private function nowMilliseconds(): int
    {
        return (int) now()->getPreciseTimestamp(3);
    }

    private function storeCooldown(int $seconds): void
    {
        $now = $this->nowMilliseconds();
        $until = $now + ($seconds * 1000);
        $current = (int) $this->cache->get($this->key('cooldown-until'), 0);

        $this->cache->put(
            $this->key('cooldown-until'),
            max($current, $until),
            now()->addSeconds(max($seconds, (int) config('services.vereinsflieger.rate_limit.cooldown_seconds', 180)) + 60),
        );
    }

    private function secondsFromMilliseconds(int $milliseconds): int
    {
        return max(1, (int) ceil($milliseconds / 1000));
    }
}
