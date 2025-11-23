<?php

namespace App\Http\Controllers\Api\V1\Bulk;

use App\Actions\Bulk\BulkImportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bulk\BulkImportRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    public function __construct(
        private readonly BulkImportAction $bulkImportAction
    ) {}

    /**
     * Handle bulk import of multiple resources.
     */
    public function __invoke(BulkImportRequest $request): JsonResponse
    {
        $results = $this->bulkImportAction->execute($request->validated());

        $hasFailures = collect($results)->contains(fn ($result) => $result['failed'] > 0);

        return response()->json([
            'data' => [
                'type' => 'bulk-import-results',
                'attributes' => [
                    'success' => ! $hasFailures,
                    'results' => $results,
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ], $hasFailures ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK);
    }
}
