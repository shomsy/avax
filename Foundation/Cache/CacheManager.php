<?php

declare(strict_types=1);

namespace Avax\Cache;

use DateInterval;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

/**
 * Class CacheManager
 *
 * Manages caching operations and delegates them to the backend.
 */
readonly class CacheManager implements CacheInterface
{
    public function __construct(
        private CacheBackendInterface $cacheBackend,
        private LoggerInterface       $logger
    ) {}

    public function get(string $key, mixed $default = null) : mixed
    {
        try {
            return $this->cacheBackend->get(key: $key, default: $default);
        } catch (Throwable $e) {
            $this->logger->error(message: 'Cache get failed', context: ['key' => $key, 'error' => $e->getMessage()]);

            return $default;
        }
    }

    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        try {
            return $this->cacheBackend->set(key: $key, value: $value, ttl: $ttl);
        } catch (Throwable $e) {
            $this->logger->error(message: 'Cache set failed', context: ['key' => $key, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function delete(string $key) : bool
    {
        try {
            return $this->cacheBackend->delete($key);
        } catch (Throwable $e) {
            $this->logger->error(message: 'Cache delete failed', context: ['key' => $key, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function clear() : bool
    {
        try {
            return $this->cacheBackend->clear();
        } catch (Throwable $e) {
            $this->logger->error(message: 'Cache clear failed', context: ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function getMultiple(iterable $keys, mixed $default = null) : iterable
    {
        try {
            return $this->cacheBackend->getMultiple(keys: $keys, default: $default);
        } catch (Throwable $e) {
            $this->logger->error(message: 'Cache getMultiple failed', context: ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null) : bool
    {
        try {
            return $this->cacheBackend->setMultiple(values: $values, ttl: $ttl);
        } catch (Throwable $e) {
            $this->logger->error(message: 'Cache setMultiple failed', context: ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function deleteMultiple(iterable $keys) : bool
    {
        try {
            return $this->cacheBackend->deleteMultiple($keys);
        } catch (Throwable $e) {
            $this->logger->error(message: 'Cache deleteMultiple failed', context: ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function has(string $key) : bool
    {
        try {
            return $this->cacheBackend->has($key);
        } catch (Throwable $e) {
            $this->logger->error(message: 'Cache has failed', context: ['key' => $key, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getBackend() : CacheBackendInterface
    {
        return $this->cacheBackend;
    }
}
