<?php

namespace App\Infrastructure\Cache;

interface CacheServiceInterface
{
    public function get(string $key);

    public function set(string $key, $value, ?int $ttl = null): bool;

    public function has(string $key): bool;

    public function delete(string $key): bool;

    public function clear(): bool;
}