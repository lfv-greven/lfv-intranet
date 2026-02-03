<?php

namespace App\Jobs\Mattermost;

use App\Services\MattermostClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeactivateUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly string $userId,
        public readonly string $fullName,
    ) {}

    public function handle(MattermostClient $client): void
    {
        if ($client->deactivateUser($this->userId)) {
            Log::info('Mattermost user deactivated.', [
                'user_id' => $this->userId,
                'name' => $this->fullName,
            ]);
        }
    }
}
