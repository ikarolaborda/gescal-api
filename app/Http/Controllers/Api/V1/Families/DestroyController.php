<?php

namespace App\Http\Controllers\Api\V1\Families;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DestroyController extends Controller
{
    public function __invoke(int $id): JsonResponse
    {
        $family = Family::find($id);

        if (! $family) {
            return response()->json(ErrorFormatterService::notFound('Family'), 404);
        }

        // Soft delete the family
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

        return response()->json(null, 204);
    }
}
