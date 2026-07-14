<?php

namespace App\Jobs\Middleware;

use App\Exceptions\VereinsfliegerDeferred;
use Closure;

class ReleaseVereinsfliegerDeferred
{
    public function handle(object $job, Closure $next): void
    {
        try {
            $next($job);
        } catch (VereinsfliegerDeferred $exception) {
            $job->release($exception->retryAfter + random_int(0, 2));
        }
    }
}
