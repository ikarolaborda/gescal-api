<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'cancellation_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'cancellation_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the identifier that will be stored in the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'roles' => $this->roles()->pluck('slug')->toArray(),
            'email' => $this->email,
        ];
    }

    /**
     * Get the organization that the user belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user's organization-specific roles.
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(\App\Models\UserRole::class);
    }

    /**
     * Get the roles assigned to this user (legacy relationship for existing system).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps()
            ->withPivot('assigned_at', 'assigned_by');
    }

    /**
     * Scope query to only admins for a specific organization.
     */
    public function scopeAdminsByOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId)
            ->whereHas('userRoles', function ($q) {
                $q->whereIn('role_name', [
                    UserRole::OrganizationAdmin->value,
                    UserRole::OrganizationSuperAdmin->value,
                ]);
            });
    }

    /**
     * Check if user has a specific role.
     */
    public function hasUserRole(string $roleName): bool
    {
        return $this->userRoles()->where('role_name', $roleName)->exists();
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(string $roleName): void
    {
        $this->userRoles()->firstOrCreate(['role_name' => $roleName]);
    }

    /**
     * Check if user is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending;
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /**
     * Check if user is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === UserStatus::Rejected;
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isCoordinator(): bool
    {
        return $this->role === UserRole::Coordinator;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSocialWorker(): bool
    {
        return $this->role === UserRole::SocialWorker;
    }

    public function submittedApprovalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'submitted_by_user_id');
    }

    public function decidedApprovalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'decided_by_user_id');
    }
}
