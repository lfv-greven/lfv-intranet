<?php

namespace App\Exceptions;

use RuntimeException;

class VereinsfliegerDeferred extends RuntimeException
{
    public function __construct(
        public readonly int $retryAfter,
        public readonly string $reason,
    ) {
        parent::__construct('Vereinsflieger request deferred: '.$reason);
    }
}
