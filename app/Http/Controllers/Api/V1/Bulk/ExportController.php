<?php

namespace App\Http\Controllers\Api\V1\Bulk;

use App\Actions\Bulk\BulkExportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bulk\BulkExportRequest;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly BulkExportAction $bulkExportAction
    ) {}

    /**
     * Handle bulk export of multiple resources.
     */
    public function __invoke(BulkExportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->bulkExportAction->execute(
            types: $validated['types'],
            filters: $validated['filters'] ?? []
        );

        return response()->json($result);
    }
}
