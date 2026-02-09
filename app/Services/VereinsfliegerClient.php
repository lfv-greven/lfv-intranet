<?php

namespace App\Services;

use App\External\Vereinsflieger;

class VereinsfliegerClient
{
    public function callWithRetry(callable $callback): array
    {
        [$success, $status, $response] = $this->call($callback);

        if ($status === 401) {
            [$success, $status, $response] = $this->call($callback);
        }

        return [$success, $status, $response];
    }

    public function call(callable $callback): array
    {
        $vf = $this->make();
        $success = (bool) $callback($vf);

        return [$success, $vf->GetHttpStatusCode(), $vf->GetResponse()];
    }

    private function make(): Vereinsflieger
    {
        $vf = new Vereinsflieger;
        $vf->SignIn(
            config('services.vereinsflieger.username'),
            config('services.vereinsflieger.password'),
        );

        return $vf;
    }
}
