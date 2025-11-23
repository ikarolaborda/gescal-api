<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserApprovedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  array<string>  $roles
     */
    public function __construct(
        public User $user,
        public array $roles
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'GESCAL Account Approved - Access Granted',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-approved',
            with: [
                'userName' => $this->user->name,
                'organizationName' => $this->user->organization->name,
                'roles' => $this->formatRoles($this->roles),
                'loginUrl' => config('app.frontend_url', config('app.url')) . '/login',
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

    /**
     * Format roles for display.
     *
     * @param  array<string>  $roles
     * @return array<string>
     */
    protected function formatRoles(array $roles): array
    {
        $roleLabels = [
            'social_worker' => 'Social Worker',
            'coordinator' => 'Coordinator',
            'organization_admin' => 'Organization Administrator',
            'organization_super_admin' => 'Organization Super Administrator',
        ];

        return array_map(fn ($role) => $roleLabels[$role] ?? ucwords(str_replace('_', ' ', $role)), $roles);
    }
}
