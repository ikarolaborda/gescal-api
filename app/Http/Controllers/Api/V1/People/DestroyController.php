<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DestroyController extends Controller
{
    public function __invoke(int $id): JsonResponse
    {
        $person = Person::find($id);

        if (! $person) {
            return response()->json(ErrorFormatterService::notFound('Person'), 404);
        }

        // Soft delete the person
        DB::transaction(function () use ($person) {
            $person->delete();

            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'soft_deleted',
                'auditable_type' => Person::class,
                'auditable_id' => $person->id,
                'comment' => 'Person soft deleted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return response()->json(null, 204);
    }
}
