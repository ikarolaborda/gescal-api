<?php

namespace App\Mail;

use App\Models\CaseRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CaseCreatedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public CaseRecord $case,
        public string $actionUrl,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Novo Caso #{$this->case->dc_number}/{$this->case->dc_year} Criado",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.case-created',
            with: [
                'caseNumber' => "{$this->case->dc_number}/{$this->case->dc_year}",
                'family' => $this->case->family,
                'occurrence' => $this->case->occurrence,
                'serviceDate' => $this->case->service_date->format('d/m/Y'),
                'actionUrl' => $this->actionUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
