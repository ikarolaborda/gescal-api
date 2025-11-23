<?php

namespace App\Mail;

use App\Models\Benefit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BenefitGrantedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Benefit $benefit,
        public string $actionUrl,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $recipientName = $this->benefit->person?->full_name ?? $this->benefit->family?->responsiblePerson?->full_name ?? 'Beneficiário';

        return new Envelope(
            subject: "Benefício Concedido - {$this->benefit->benefitProgram->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.benefit-granted',
            with: [
                'benefitProgram' => $this->benefit->benefitProgram,
                'beneficiary' => $this->benefit->person ?? $this->benefit->family,
                'value' => $this->benefit->value,
                'startedAt' => $this->benefit->started_at?->format('d/m/Y'),
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
