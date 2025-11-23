<?php

namespace App\Enums;

enum UserRole: string
{
    case SocialWorker = 'ROLE_SOCIAL_WORKER';
    case Coordinator = 'ROLE_COORDINATOR';
    case Admin = 'ROLE_ADMIN';
    case OrganizationAdmin = 'ROLE_ORGANIZATION_ADMIN';
    case OrganizationSuperAdmin = 'ROLE_ORGANIZATION_SUPER_ADMIN';

    public function canReview(): bool
    {
        return in_array($this, [self::Coordinator, self::Admin, self::OrganizationAdmin, self::OrganizationSuperAdmin]);
    }

    public function canApprove(): bool
    {
        return in_array($this, [self::Coordinator, self::Admin, self::OrganizationAdmin, self::OrganizationSuperAdmin]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Admin, self::OrganizationSuperAdmin]);
    }

    public function canRevoke(): bool
    {
        return in_array($this, [self::Admin, self::OrganizationSuperAdmin]);
    }

    public function canManageOrganization(): bool
    {
        return in_array($this, [self::OrganizationAdmin, self::OrganizationSuperAdmin]);
    }

    public function canManageUsers(): bool
    {
        return in_array($this, [self::OrganizationAdmin, self::OrganizationSuperAdmin]);
    }

    public function label(): string
    {
        return match ($this) {
            self::SocialWorker => 'Social Worker',
            self::Coordinator => 'Coordinator',
            self::Admin => 'Administrator',
            self::OrganizationAdmin => 'Organization Administrator',
            self::OrganizationSuperAdmin => 'Organization Super Administrator',
        };
    }
}
