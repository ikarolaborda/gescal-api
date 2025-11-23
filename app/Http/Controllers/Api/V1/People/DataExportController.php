<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Actions\People\ExportPersonDataAction;
use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\JsonResponse;

class DataExportController extends Controller
{
    public function __construct(
        private readonly ExportPersonDataAction $exportPersonDataAction
    ) {}

    /**
     * Export all personal data for LGPD Article 18 compliance.
     *
     * Allows the person (or authorized user) to download all their data.
     */
    public function __invoke(string $id): JsonResponse
    {
        $person = Person::findOrFail($id);

        // Authorization: User can only export their own data or admin/coordinator can export any
        $this->authorize('viewAny', Person::class);

        $exportData = $this->exportPersonDataAction->execute($person);

        return response()->json($exportData);
    }
}
