<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class CacheInvalidationService
{
    /**
     * Invalidate cache for a specific reference data type.
     */
    public function invalidateReferenceData(string $type): void
    {
        Cache::tags(['reference_data', $type])->flush();
    }

    /**
     * Invalidate all reference data caches.
     */
    public function invalidateAllReferenceData(): void
    {
        // This would require knowing all reference data tags, or a global tag
        // For now, we'll rely on specific invalidation or a broader flush if needed.
        // Example: Cache::tags(['reference_data'])->flush(); if all reference data uses a common 'reference_data' tag
    }
}
