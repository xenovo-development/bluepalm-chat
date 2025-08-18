<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class MarketplaceResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Conversation $conversation;
    protected array $messageDetails;

    public function __construct(Conversation $conversation, $messageDetails)
    {
        $this->conversation = $conversation;
        $this->messageDetails = $messageDetails;
    }

    public function handle(): void
    {
        $message = new Message($this->messageDetails);
        $this->conversation->messages()->save($message);
    }

    /**
     * Fires when there is an error processing the job.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('MarketplaceResponse job failed', [
            'conversationId' => $this->conversation->id,
            'error' => $exception->getMessage(),
            'stackTrace' => $exception->getTraceAsString()
        ]);
    }
}
