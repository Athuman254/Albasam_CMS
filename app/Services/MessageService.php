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
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\MessageException;

class MessageService
{
    public function createDraft(User $user, array $data): \App\Models\Campaign
    {
        return DB::transaction(function () use ($user, $data) {
            $campaign = \App\Models\Campaign::create([
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
            $senderName = auth()->user()?->name ?? config('app.name', 'School Management');
            $content .= "\n\nBest regards,\n" . $senderName;
        }

        // Add mandatory opt-out message
        $content .= "\n\nReply STOP to opt out.";

        return $content;
    }

    public function sendPaymentReceipt(Student $student, $amount, $reference): void
    {
        try {
            // Find phone number - Guardian first, then student
            $phoneNumber = null;
            $guardian = $student->guardians()->first();

            if ($guardian && $guardian->phone) {
                $phoneNumber = $guardian->phone;
            } elseif ($student->phone) {
                $phoneNumber = $student->phone;
            }

            if (!$phoneNumber) {
                Log::warning("No phone number found for student {$student->id} to send payment receipt.");
                return;
            }

            // Clean phone number (remove spaces, etc.) - simple version
            $phoneNumber = str_replace([' ', '-', '(', ')', '+'], '', $phoneNumber);

            $balance = $student->balance;
            $studentName = $student->full_name;

            $content = "Dear Parent/Guardian, we have received KES " . number_format($amount, 2) .
                " for {$studentName}. Ref: {$reference}. New Balance: KES " . number_format($balance, 2) .
                ". Thank you for your payment!";

            $this->logAndQueueSystemSms($phoneNumber, $student, $content);

            Log::info("Payment receipt SMS queued for student {$student->id}, Amount: {$amount}");
        } catch (\Exception $e) {
            Log::error("Failed to send payment receipt SMS: " . $e->getMessage());
        }
    }

    public function sendFeeAssignmentNotification(Student $student, \App\Models\Fee $fee): void
    {
        try {
            $phoneNumber = $this->getStudentOrGuardianPhone($student);
            if (!$phoneNumber) return;

            $studentName = $student->full_name;
            $feeType = ucfirst($fee->fee_type);
            $amount = number_format($fee->amount, 2);
            $dueDate = $fee->due_date->format('M j, Y');

            $content = "Dear Parent/Guardian, a new fee ({$feeType}) of KES {$amount} has been assigned to {$studentName}. Due Date: {$dueDate}. Please ensure timely payment. Thank you!";

            $this->logAndQueueSystemSms($phoneNumber, $student, $content);
            Log::info("Fee assignment SMS queued for student {$student->id}, Fee: {$fee->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send fee assignment SMS: " . $e->getMessage());
        }
    }

    public function sendFeeReminder(Student $student, $balance, $customMessage = null): void
    {
        try {
            $phoneNumber = $this->getStudentOrGuardianPhone($student);
            if (!$phoneNumber) return;

            $studentName = $student->full_name;
            $amount = number_format($balance, 2);

            $content = $customMessage ?? "Dear Parent/Guardian, this is a reminder that {$studentName} has an outstanding fee balance of KES {$amount}. Please settle at your earliest convenience. Thank you!";

            $this->logAndQueueSystemSms($phoneNumber, $student, $content);
            Log::info("Fee reminder SMS queued for student {$student->id}, Balance: {$balance}");
        } catch (\Exception $e) {
            Log::error("Failed to send fee reminder SMS: " . $e->getMessage());
        }
    }

    private function getStudentOrGuardianPhone(Student $student): ?string
    {
        $phoneNumber = null;
        $guardian = $student->guardians()->first();

        if ($guardian && $guardian->phone) {
            $phoneNumber = $guardian->phone;
        } elseif ($student->phone) {
            $phoneNumber = $student->phone;
        }

        if (!$phoneNumber) {
            Log::warning("No phone number found for student {$student->id}");
            return null;
        }

        return str_replace([' ', '-', '(', ')', '+'], '', $phoneNumber);
    }

    private function logAndQueueSystemSms(string $phoneNumber, Student $student, string $content): void
    {
        DB::transaction(function () use ($phoneNumber, $student, $content) {
            // Ensure a "System Notifications" campaign exists
            $campaign = Campaign::firstOrCreate(
                ['name' => 'System Notifications'],
                [
                    'status' => 'active',
                    'description' => 'Automatically generated system notifications (Receipts, Fee Alerts, etc.)',
                    'settings' => ['include_salutation' => false, 'include_signature' => false]
                ]
            );

            // Find or create contact
            $contact = Contact::firstOrCreate(
                ['phone_number' => $phoneNumber],
                [
                    'name' => $student->full_name,
                    'type' => 'student',
                    'related_id' => $student->id,
                    'created_at' => now()
                ]
            );

            // Create message
            $message = Message::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'content' => $content . "\n\nReply STOP to opt out.",
                'status' => 'pending',
                'scheduled_at' => now()
            ]);

            // Dispatch job
            SendMessageJob::dispatch($message);
        });
    }
}
