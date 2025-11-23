<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'data' => [
                'type' => 'users',
                'id' => (string) $user->id,
                'attributes' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                ],
                'relationships' => [
                    'roles' => [
                        'data' => $user->roles->map(fn ($role) => [
                            'type' => 'roles',
                            'id' => (string) $role->id,
                        ])->toArray(),
                    ],
                ],
            ],
            'included' => $user->roles->map(fn ($role) => [
                'type' => 'roles',
                'id' => (string) $role->id,
                'attributes' => [
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                ],
            ])->toArray(),
        ], 200);
    }
}
