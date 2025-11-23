<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ReferenceDataCacheService
{
    protected int $ttl; // Time to live in seconds

    public function __construct()
    {
        $this->ttl = Config::get('cache.stores.redis.reference_data_ttl', 3600); // Default 1 hour
    }

    /**
     * Retrieve data from cache or execute callback and store it.
     */
    public function remember(string $key, array $tags, callable $callback): mixed
    {
        return Cache::tags($tags)->remember($key, $this->ttl, $callback);
    }

    /**
     * Forget data from cache by key.
     */
    public function forget(string $key, array $tags): bool
    {
        return Cache::tags($tags)->forget($key);
    }

    /**
     * Flush all cache for given tags.
     */
    public function flush(array $tags): bool
    {
        return Cache::tags($tags)->flush();
    }

    /**
     * Generate an ETag for the given data.
     */
    public function generateEtag(mixed $data): string
    {
        return md5(json_encode($data));
    }
}
