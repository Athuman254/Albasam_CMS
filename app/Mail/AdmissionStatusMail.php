<?php

namespace App\Mail;

use App\Models\AdmissionApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmissionStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public AdmissionApplication $application, public string $status) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Admission Application Update - ' . strtoupper($this->status);
        if ($this->status === 'approved') {
            $subject = 'Congratulations! Your Admission has been APPROVED';
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admission_status',
            with: [
                'name' => $this->application->first_name,
                'appNumber' => $this->application->application_number,
                'status' => $this->status,
                'notes' => $this->application->admin_notes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
