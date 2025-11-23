<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V2 Routes
|--------------------------------------------------------------------------
|
| Version 2 of the API. This file will contain future v2 endpoints.
| V1 routes remain in api.php for backward compatibility.
|
| Breaking changes from V1:
| - TBD (version 2 not yet implemented)
|
*/

Route::prefix('v2')->name('api.v2.')->group(function () {
    // V2 routes will be added here in future iterations
    // Example: Breaking changes, new features, improved JSON:API compliance

    // Placeholder health check for V2
    Route::get('/health', function () {
        return response()->json([
            'data' => [
                'type' => 'health',
                'id' => '1',
                'attributes' => [
                    'status' => 'healthy',
                    'version' => '2.0',
                    'message' => 'API V2 is available for future use',
                ],
            ],
        ]);
    })->name('health');
});
