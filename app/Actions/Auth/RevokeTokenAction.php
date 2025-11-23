<?php

namespace App\Actions\Auth;

use Tymon\JWTAuth\Facades\JWTAuth;

class RevokeTokenAction
{
    /**
     * Revoke (blacklist) the current JWT token on logout.
     */
    public function execute(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
