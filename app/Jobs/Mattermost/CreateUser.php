<?php

namespace App\Jobs\Mattermost;

use App\Mail\MattermostUserCreated;
use App\Services\MattermostClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly array $profile,
        public readonly string $fullName,
        public readonly string $email,
    ) {}

    public function handle(MattermostClient $client): void
    {
        $created = $client->createUser($this->profile);

        if (! $created) {
            return;
        }

        $bcc = config('mattermost_sync.bcc_email');

        $message = Mail::to($this->email);
        if (filled($bcc)) {
            $message->bcc($bcc);
        }

        $message->send(new MattermostUserCreated(
            name: $this->fullName,
            email: $this->email,
            password: (string) $created['password'],
        ));

        Log::info('Mattermost user created and mail sent.', [
            'email' => $this->email,
        ]);
    }
}
