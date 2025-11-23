<?php

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\AdminPendingUserNotification;
use App\Mail\UserPendingNotification;
use App\Mail\UserRegisteredNotification;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterUserAction
{
    /**
     * Execute the user registration process.
     *
     * @throws \Exception
     */
    public function execute(RegisterRequest $request): User
    {
        return DB::transaction(function () use ($request) {
            $cnpj = $request->validated('organization_cnpj');
            $organizationName = $request->validated('organization_name');

            $organization = null;
            $isFirstUser = false;

            try {
                $organization = Organization::create([
                    'name' => $organizationName,
                    'cnpj' => $cnpj,
                    'status' => 'active',
                ]);

                $isFirstUser = true;

                $totalOrganizations = Organization::count();
                if ($totalOrganizations > 1) {
                    Log::critical('SINGLE_TENANT_VIOLATION: Multiple organizations detected', [
                        'total_organizations' => $totalOrganizations,
                        'newly_created_org_id' => $organization->id,
                        'cnpj' => $cnpj,
                        'timestamp' => now()->toIso8601String(),
                    ]);

                    throw new \Exception(
                        'Single-tenant architecture violation: Only one organization is allowed per deployment.'
                    );
                }

                Log::info('New organization created', [
                    'organization_id' => $organization->id,
                    'cnpj' => $cnpj,
                    'name' => $organizationName,
                ]);
            } catch (QueryException $e) {
                if ($e->getCode() === '23000') {
                    $organization = Organization::where('cnpj', $cnpj)->firstOrFail();
                    $isFirstUser = false;

                    Log::info('User registering to existing organization', [
                        'organization_id' => $organization->id,
                        'cnpj' => $cnpj,
                    ]);
                } else {
                    throw $e;
                }
            }

            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'organization_id' => $organization->id,
                'status' => $isFirstUser ? UserStatus::Active : UserStatus::Pending,
                'cancellation_token' => $isFirstUser ? null : Str::random(64),
                'cancellation_token_expires_at' => $isFirstUser ? null : now()->addDays(7),
            ]);

            if ($isFirstUser) {
                $user->assignRole(UserRole::OrganizationSuperAdmin->value);

                $token = auth()->guard('api')->login($user);
                $user->token = $token;

                Mail::to($user->email)->queue(new UserRegisteredNotification($user, $organization));

                Log::info('First user registered as Organization Super Admin', [
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                    'email' => $user->email,
                ]);
            } else {
                $cancellationUrl = config('app.frontend_url', config('app.url'))
                    . '/auth/cancel-registration?token=' . $user->cancellation_token;

                Mail::to($user->email)->queue(
                    new UserPendingNotification($user, $cancellationUrl)
                );

                $admins = User::where('organization_id', $organization->id)
                    ->where('status', UserStatus::Active)
                    ->whereHas('userRoles', function ($query) {
                        $query->whereIn('role_name', [
                            UserRole::OrganizationAdmin->value,
                            UserRole::OrganizationSuperAdmin->value,
                        ]);
                    })
                    ->get();

                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue(
                        new AdminPendingUserNotification($admin, $user, $organization)
                    );
                }

                Log::info('Subsequent user registered with pending status', [
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                    'email' => $user->email,
                    'cancellation_token_expires_at' => $user->cancellation_token_expires_at->toIso8601String(),
                    'admins_notified' => $admins->count(),
                ]);
            }

            $user->load(['organization', 'userRoles']);

            return $user;
        });
    }
}
