<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Campaign $campaign) {
        // dd($this->campaign->messages()->where('status', 'pending'));
        // $this->test();
    }

    public function test(){
        $this->campaign->messages()
            ->where('status', 'pending')
            ->each(function ($message) {

                SendMessageJob::dispatch($message);
            });
    }
    public function handle(): void
    {
        $this->campaign->update(['status' => 'in_progress']);

        $this->campaign->messages()
            ->where('status', 'pending')
            ->each(function ($message) {

                SendMessageJob::dispatch($message);
            });
    }
}
