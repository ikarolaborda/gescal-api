<?php

namespace App\Http\Controllers\Api\V1\Families;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DestroyController extends Controller
{
    public function __invoke(int $id): JsonResponse
    {
        $family = Family::find($id);

        if (! $family) {
            return response()->json(ErrorFormatterService::notFound('Family'), Response::HTTP_NOT_FOUND);
        }

        DB::transaction(function () use ($family) {
            $family->delete();

            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'soft_deleted',
                'auditable_type' => Family::class,
                'auditable_id' => $family->id,
                'comment' => 'Family soft deleted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
