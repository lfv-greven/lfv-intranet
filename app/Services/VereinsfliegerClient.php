<?php

namespace App\Services;

use App\Enums\VereinsfliegerPriority;
use App\Exceptions\VereinsfliegerDeferred;
use App\External\Vereinsflieger;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class VereinsfliegerClient
{
    public function __construct(
        private readonly VereinsfliegerRequestGate $requestGate,
        private readonly Repository $cache,
    ) {}

    /** @return array<string, mixed>|null */
    public function loginMember(string $email, string $password): ?array
    {
        return $this->requestGate->withinLoginAttempt(function (callable $recordCredentialFailure) use ($email, $password): ?array {
            $vf = $this->make(VereinsfliegerPriority::HIGH);
            $loginSuccess = $vf->SignIn($email, $password);

            if (! $loginSuccess) {
                if ($this->isCredentialFailure($vf->GetHttpStatusCode())) {
                    $recordCredentialFailure();
                }

                return null;
            }

            $user = $vf->GetUser();

            if ($user === []) {
                if ($this->isCredentialFailure($vf->GetHttpStatusCode())) {
                    $recordCredentialFailure();
                }

                return null;
            }

            return $user;
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function loginIframe(string $accessToken): ?array
    {
        return $this->make(VereinsfliegerPriority::HIGH, $accessToken)->IframeLogin($accessToken);
    }

    /**
     * @return array{0: bool, 1: int, 2: mixed}
     */
    public function callWithRetry(VereinsfliegerPriority $priority, callable $callback): array
    {
        [$success, $status, $response, $accessToken] = $this->callWithToken($priority, $callback);

        if ($status === 401) {
            $this->forgetTokenIfCurrent($accessToken);
            [$success, $status, $response, $accessToken] = $this->callWithToken($priority, $callback);
        }

        return [$success, $status, $response];
    }

    /**
     * @return array{0: bool, 1: int, 2: mixed, 3: string}
     */
    private function callWithToken(VereinsfliegerPriority $priority, callable $callback): array
    {
        $accessToken = $this->adminAccessToken($priority);
        $vf = $this->make($priority, $accessToken);
        $success = (bool) $callback($vf);

        return [$success, $vf->GetHttpStatusCode(), $vf->GetResponse(), $accessToken];
    }

    private function adminAccessToken(VereinsfliegerPriority $priority): string
    {
        $cachedToken = $this->cachedAdminAccessToken();

        if ($cachedToken !== null) {
            return $cachedToken;
        }

        $lock = $this->cache->lock($this->requestGate->key('admin-token-refresh'), $this->requestGate->lockSeconds());
        if (! $lock->get()) {
            throw new VereinsfliegerDeferred(1, 'token_refresh');
        }

        try {
            $cachedToken = $this->cachedAdminAccessToken();

            if ($cachedToken !== null) {
                return $cachedToken;
            }

            return $this->requestGate->withinLoginAttempt(function (callable $recordCredentialFailure) use ($priority): string {
                $vf = $this->make($priority);
                $success = $vf->SignIn(
                    config('services.vereinsflieger.username'),
                    config('services.vereinsflieger.password'),
                );

                if (! $success) {
                    if ($this->isCredentialFailure($vf->GetHttpStatusCode())) {
                        $recordCredentialFailure();
                    }

                    throw new RuntimeException('Vereinsflieger service login failed (HTTP '.$vf->GetHttpStatusCode().').');
                }

                $accessToken = $vf->GetAccessToken();
                if (! is_string($accessToken) || $accessToken === '') {
                    throw new RuntimeException('Vereinsflieger service login returned no access token.');
                }

                $this->cache->put(
                    $this->requestGate->key('admin-token'),
                    Crypt::encryptString($accessToken),
                    now()->addSeconds((int) config('services.vereinsflieger.token_ttl_seconds', 3600)),
                );

                return $accessToken;
            });
        } finally {
            $lock->release();
        }
    }

    private function forgetTokenIfCurrent(string $accessToken): void
    {
        $lock = $this->cache->lock($this->requestGate->key('admin-token-refresh'), $this->requestGate->lockSeconds());

        if (! $lock->get()) {
            return;
        }

        try {
            if ($this->cachedAdminAccessToken() === $accessToken) {
                $this->cache->forget($this->requestGate->key('admin-token'));
            }
        } finally {
            $lock->release();
        }
    }

    private function cachedAdminAccessToken(): ?string
    {
        $encryptedToken = $this->cache->get($this->requestGate->key('admin-token'));

        if (! is_string($encryptedToken) || $encryptedToken === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedToken);
        } catch (DecryptException) {
            $this->cache->forget($this->requestGate->key('admin-token'));

            return null;
        }
    }

    private function make(VereinsfliegerPriority $priority, ?string $accessToken = null): Vereinsflieger
    {
        return new Vereinsflieger($this->requestGate, $priority, $accessToken);
    }

    private function isCredentialFailure(int $status): bool
    {
        return in_array($status, [400, 401, 403], true);
    }
}
