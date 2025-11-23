<?php

namespace App\Http\Controllers\Api\V1\Organizations;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PendingUsersController extends Controller
{
    /**
     * Display a listing of pending users for the organization.
     */
    public function __invoke(string $org): AnonymousResourceCollection
    {
        $pendingUsers = User::where('organization_id', $org)
            ->where('status', UserStatus::Pending)
            ->orderBy('created_at', 'desc')
            ->get();

        return UserResource::collection($pendingUsers);
    }
}
