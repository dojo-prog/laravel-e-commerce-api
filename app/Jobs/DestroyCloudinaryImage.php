<?php

namespace App\Jobs;

use App\Services\CloudinaryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DestroyCloudinaryImage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int> */
    public array $backoff = [5, 15, 60];

    public function __construct(
        public string $publicId
    ) {
        $this->afterCommit();
    }

    public function handle(CloudinaryService $cloudinary): void
    {
        $cloudinary->destroy($this->publicId);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::warning("Failed to destroy Cloudinary asset after all retries: {$this->publicId}", [
            'public_id' => $this->publicId,
            'exception' => (string) $exception,
        ]);
    }
}
