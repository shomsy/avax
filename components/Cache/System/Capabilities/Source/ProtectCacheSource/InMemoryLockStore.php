<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Cache\System\Foundation\Time\Clock;
use Avax\Components\Cache\System\Foundation\Time\SystemClock;

final class InMemoryLockStore implements CacheLockStore
{
    /** @var array<string, CacheLockOwner> */
    private array $locks = [];

    public function __construct(
        private Clock $clock = new SystemClock(),
        /** @var array<string, CacheLockOwner> */
        array         $locks = []
    )
    {
        $this->locks = $locks;
    }

    public function acquire(string $key, string $owner, int $ttlSeconds) : bool
    {
        if ($this->isAcquired(key: $key)) {
            $existingOwner = $this->locks[$key] ?? null;

            if ($existingOwner !== null && ! $existingOwner->isExpired(clock: $this->clock)) {
                return $existingOwner->ownerId === $owner;
            }
        }

        $this->locks[$key] = CacheLockOwner::current(
            ownerId   : $owner,
            ttlSeconds: $ttlSeconds,
            clock     : $this->clock
        );

        return true;
    }

    public function isAcquired(string $key) : bool
    {
        if (! isset($this->locks[$key])) {
            return false;
        }

        $owner = $this->locks[$key];

        if ($owner->isExpired(clock: $this->clock)) {
            unset($this->locks[$key]);

            return false;
        }

        return true;
    }

    public function release(string $key, string $owner) : void
    {
        $existingOwner = $this->locks[$key] ?? null;

        if ($existingOwner !== null && $existingOwner->ownerId === $owner) {
            unset($this->locks[$key]);
        }
    }

    public function getOwner(string $key) : CacheLockOwner|null
    {
        if (! $this->isAcquired(key: $key)) {
            return null;
        }

        return $this->locks[$key];
    }

    public function releaseAll() : void
    {
        $this->locks = [];
    }
}