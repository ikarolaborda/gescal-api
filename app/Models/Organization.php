<?php

namespace App\Models;

use App\Enums\OrganizationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cnpj',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrganizationStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function activeUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('status', 'active');
    }

    public function pendingUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('status', 'pending');
    }

    public function isActive(): bool
    {
        return $this->status === OrganizationStatus::Active;
    }

    public function canAcceptUsers(): bool
    {
        return $this->isActive() && ! $this->trashed();
    }
}
