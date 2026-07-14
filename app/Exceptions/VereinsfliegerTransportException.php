<?php

namespace App\Exceptions;

use RuntimeException;

class VereinsfliegerTransportException extends RuntimeException
{
    public function __construct(
        public readonly string $resource,
        string $message,
    ) {
        parent::__construct('Vereinsflieger transport error for '.$resource.': '.$message);
    }
}
