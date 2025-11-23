<?php

namespace App\Actions\Auth;

use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenAction
{
    /**
     * Refresh the JWT token.
     *
     * @return array{token: string, token_type: string, expires_in: int}
     */
    public function execute(): array
    {
        $newToken = JWTAuth::refresh();

        return [
            'token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') * 60,
        ];
    }
}
