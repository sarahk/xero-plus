<?php

namespace App\Models\Traits;

// all the functions that support a datatable
trait CacheHandler
{
    function cache_key(string $url, array $params = []): string
    {
        return hash('sha256', $url . '|' . json_encode($params));
    }

    function cache_get(string $key, int $ttl = 3600): ?array
    {
        $cache_dir = __DIR__ . '/../../../storage/cache';
        $file = $cache_dir . "/$key.json";
        if (is_file($file) && (time() - filemtime($file) < $ttl)) {
            $json = file_get_contents($file);
            return $json !== false ? json_decode($json, true) : null;
        }
        return null;
    }

    function cache_put(string $key, array $data): void
    {
        $cache_dir = __DIR__ . '/../../../storage/cache';
        if (!is_dir($cache_dir)) mkdir($cache_dir, 0775, true);
        file_put_contents($cache_dir . "/$key.json", json_encode($data, JSON_PRETTY_PRINT));
    }
}