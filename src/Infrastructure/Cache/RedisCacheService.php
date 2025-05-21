<?php

namespace App\Infrastructure\Cache;

class RedisCacheService implements CacheServiceInterface
{
    private \Redis $redis;
    private string $prefix;

    public function __construct(
        string $host = 'servidor_redis',
        int $port = 6379,
        string $prefix = 'books_api:',
        string $password = ''
    ) {
        $this->redis = new \Redis();

        try {
            $this->redis->connect($host, $port);

            if (!empty($password)) {
                $this->redis->auth($password);
            }

            $this->prefix = $prefix;
        } catch (\Exception $e) {
            error_log('Error connecting to Redis: ' . $e->getMessage());
        }
    }


    public function get(string $key)
    {
        try {
            $value = $this->redis->get($this->prefix . $key);

            if ($value === false) {
                return null;
            }

            return unserialize($value);
        } catch (\Exception $e) {
            error_log('Redis get error: ' . $e->getMessage());
            return null;
        }
    }

    public function set(string $key, $value, ?int $ttl = null): bool
    {
        try {
            $serialized = serialize($value);

            if ($ttl) {
                return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
            }

            return $this->redis->set($this->prefix . $key, $serialized);
        } catch (\Exception $e) {
            error_log('Redis set error: ' . $e->getMessage());
            return false;
        }
    }

    public function has(string $key): bool
    {
        try {
            return $this->redis->exists($this->prefix . $key);
        } catch (\Exception $e) {
            error_log('Redis exists error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(string $key): bool
    {
        try {
            return $this->redis->del($this->prefix . $key) > 0;
        } catch (\Exception $e) {
            error_log('Redis delete error: ' . $e->getMessage());
            return false;
        }
    }

    public function clear(): bool
    {
        try {
            $keys = $this->redis->keys($this->prefix . '*');

            if (empty($keys)) {
                return true;
            }

            return $this->redis->del($keys) > 0;
        } catch (\Exception $e) {
            error_log('Redis clear error: ' . $e->getMessage());
            return false;
        }
    }
}