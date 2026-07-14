<?php

namespace App\Jobs\Concerns;

use App\Jobs\Middleware\ReleaseVereinsfliegerDeferred;

trait UsesVereinsfliegerLowPriorityQueue
{
    public int $tries = 10;

    public function middleware(): array
    {
        return [new ReleaseVereinsfliegerDeferred];
    }

    protected function configureVereinsfliegerLowPriorityQueue(): void
    {
        $this->onQueue('vereinsflieger-low');
    }
}
