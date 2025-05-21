<?php

namespace App\Infrastructure\Cache;

class CacheFactory
{
    public static function create(string $type = 'redis', array $config = []): CacheServiceInterface
    {
        switch ($type) {
            case 'redis':
                $host = $config['host'] ?? 'servidor_redis';
                $port = $config['port'] ?? 6379;
                $prefix = $config['prefix'] ?? 'books_api:';
                $password = $config['password'] ?? '';

                return new RedisCacheService($host, $port, $prefix, $password);

            default:
                throw new \InvalidArgumentException("Tipo de caché no soportado: {$type}");
        }
    }
}