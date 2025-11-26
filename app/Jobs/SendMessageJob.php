<?php
namespace App\Jobs;

use App\Models\Message;
use App\Models\Messagelog;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 180, 300];

    public function __construct(private Message $message) {
        // dd($message);
    }

    public function handle(): void
    {
        try {
            $this->message->update(['status' => 'sending']);

            $payload = [
                "data" => [
                    [
                        "message_bag" => [
                            "numbers" => "0794239651",
                            "message" => "test",
                            "sender" => config('services.ujembe.sender_id', 'UjumbeSMS')
                        ]
                    ]
                ]
            ];
            // $payload = json_encode($payload);

            // interation here
            info($payload);
            // info( env('UJUMBE_API_KEY'));

            $response = Http::withHeaders([
                'X-Authorization' => env('UJUMBE_API_KEY'),
                'email' => 'info@ecobiz.co.ke',
                'Cache-Control' => 'no-cache'
            ])->post('http://ujumbesms.co.ke/api/messaging', $payload);
            info($response);
            if ($response->json('status.type') === 'success') {
                // Update message status to sent
                $this->message->update([
                    'status' => 'sent',
                    'sent_at' => now()
                ]);

                // Log the successful send
                MessageLog::create([
                    'message_id' => $this->message->id,
                    'event_type' => 'sent',
                    'event_data' => [
                        'provider_message_id' => $response->json('meta.date_time.date'),
                        'attempt' => $this->attempts(),
                        'credits_deducted' => $response->json('meta.credits_deducted'),
                        'available_credits' => $response->json('meta.available_credits')
                    ]
                ]);
            } else {
                throw new \Exception($response->json('status.description', 'Unknown error occurred'));
            }

            // $this->message->update([
            //     'status' => 'sent',
            //     'sent_at' => now()
            // ]);

            // MessageLog::create([
            //     'message_id' => $this->message->id,
            //     'event_type' => 'sent',
            //     'event_data' => [
            //         'provider_message_id' => $response->id ?? null,
            //         'attempt' => $this->attempts()
            //     ]
            // ]);

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
