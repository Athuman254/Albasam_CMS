<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\OptOut;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Campaign;
use App\Jobs\SendMessageJob;
use App\Jobs\ProcessCampaignJob;
use Illuminate\Support\Facades\DB;
use App\Exceptions\MessageException;

class MessageService
{
    public function createDraft(User $user, array $data): Campaign
    {
        return DB::transaction(function () use ($user, $data) {
            $campaign = Campaign::create([
                'user_id' => $user->id,
                'name' => $data['campaign']['name'],
                'description' => $data['campaign']['description'] ?? null,
                'status' => 'draft',
                'settings' => [
                    'include_salutation' => $data['include_salutation'] ?? false,
                    'include_signature' => $data['include_signature'] ?? false,
                    'template_id' => $data['template_id'] ?? null,
                ]
            ]);
            dd($campaign);
            $this->createMessages($campaign, $data['recipients'], $data['content']);

            return $campaign;
        });
    }

    public function sendMessages(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            // Create campaign
            $campaign = Campaign::create([
                'user_id' => $user->id,
                'name' => $data['campaign']['name'],
                'description' => $data['campaign']['description'] ?? null,
                'status' => 'scheduled',
                'scheduled_at' => $data['schedule_date'] ?? now(),
                'settings' => [
                    'include_salutation' => $data['include_salutation'] ?? false,
                    'include_signature' => $data['include_signature'] ?? false,
                    'template_id' => $data['template_id'] ?? null,
                ]
            ]);

            // Create messages
            $messages = $this->createMessages($campaign, $data['recipients'], $data['content']);

            if ($data['schedule_date'] ?? false) {
                ProcessCampaignJob::dispatch($campaign)
                    ->delay(Carbon::parse($data['schedule_date']));
            } else {
                ProcessCampaignJob::dispatch($campaign);
            }

            return [
                'campaign_id' => $campaign->id,
                'message_count' => count($messages)
            ];
        });
    }

    private function createMessages(Campaign $campaign, array $recipients, string $content): array
    {
        $messages = [];

        foreach ($recipients as $phoneNumber) {
            // Find or create contact
            $contact = Contact::firstOrCreate(
                ['phone_number' => $phoneNumber],
                ['created_at' => now()]
            );

            // Check for opt-out
            if (OptOut::where('phone_number', $phoneNumber)->exists()) {
                continue;
            }

            $messages[] = Message::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'template_id' => $campaign->settings['template_id'] ?? null,
                'content' => $this->prepareMessageContent(
                    $content,
                    $contact,
                    $campaign->settings
                ),
                'status' => 'pending',
                'scheduled_at' => $campaign->scheduled_at
            ]);
        }

        return $messages;
    }

    private function prepareMessageContent(string $content, Contact $contact, array $settings): string
    {
        if ($settings['include_salutation'] && $contact->first_name) {
            $content = "Hi {$contact->first_name},\n\n" . $content;
        }

        if ($settings['include_signature']) {
            $content .= "\n\nBest regards,\n" . auth()->user()->name;
        }

        // Add mandatory opt-out message
        $content .= "\n\nReply STOP to opt out.";

        return $content;
    }
}
