<?php
namespace App\Jobs;

use App\Models\Message;
use App\Models\Messagelog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 180, 300];

    public function __construct(private Message $message) {}

    public function handle(): void
    {
        try {
            $this->message->update(['status' => 'sending']);

            // interation

            $this->message->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            MessageLog::create([
                'message_id' => $this->message->id,
                'event_type' => 'sent',
                'event_data' => [
                    'provider_message_id' => $response->id ?? null,
                    'attempt' => $this->attempts()
                ]
            ]);

        } catch (\Exception $e) {
            $this->message->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            MessageLog::create([
                'message_id' => $this->message->id,
                'event_type' => 'failed',
                'event_data' => [
                    'error' => $e->getMessage(),
                    'attempt' => $this->attempts()
                ]
            ]);

            throw $e;
        }
    }
}
