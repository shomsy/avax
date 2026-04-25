<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCacheSource;

final class InMemoryLockStore implements CacheLockStore
{
    /** @var array<string, CacheLockOwner> */
    private array $locks = [];

    public function __construct(
        array $locks = []
    )
    {
        $this->locks = $locks;
    }

    public function acquire(string $key, int $ttlSeconds) : bool
    {
        if ($this->isAcquired($key)) {
            $owner = $this->locks[$key] ?? null;

            if ($owner !== null && ! $owner->isExpired()) {
                return false;
            }
        }

        $this->locks[$key] = CacheLockOwner::current(
            ownerId   : uniqid(more_entropy: true),
            ttlSeconds: $ttlSeconds
        );

        return true;
    }

    public function isAcquired(string $key) : bool
    {
        if (! isset($this->locks[$key])) {
            return false;
        }

        $owner = $this->locks[$key];

        if ($owner->isExpired()) {
            unset($this->locks[$key]);

            return false;
        }

        return true;
    }

    public function release(string $key) : void
    {
        unset($this->locks[$key]);
    }

    public function getOwner(string $key) : ?CacheLockOwner
    {
        if (! $this->isAcquired($key)) {
            return null;
        }

        return $this->locks[$key];
    }

    public function releaseAll() : void
    {
        $this->locks = [];
    }
}