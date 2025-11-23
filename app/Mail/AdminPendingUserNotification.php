<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPendingUserNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $admin,
        public User $pendingUser,
        public Organization $organization
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'GESCAL - New User Pending Approval',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-pending-user',
            with: [
                'adminName' => $this->admin->name,
                'pendingUserName' => $this->pendingUser->name,
                'pendingUserEmail' => $this->pendingUser->email,
                'organizationName' => $this->organization->name,
                'registrationDate' => $this->pendingUser->created_at->format('d/m/Y H:i'),
                'approveUrl' => config('app.frontend_url', config('app.url')) . "/admin/users/{$this->pendingUser->id}/approve",
                'rejectUrl' => config('app.frontend_url', config('app.url')) . "/admin/users/{$this->pendingUser->id}/reject",
                'supportEmail' => config('mail.support_email', 'support@gescal.local'),
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
