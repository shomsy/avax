<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Override;

final class InMemoryLockStore implements CacheLockStore
{
    public function __construct(
        private readonly Clock $clock = new SystemClock(),
        /** @var array<string, CacheLockOwner> */
        private array          $locks = []
    )
    {
    }

    #[Override]
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
            clock     : $this->clock,
        );

        return true;
    }

    #[Override]
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

    #[Override]
    public function release(string $key, string $owner) : void
    {
        $existingOwner = $this->locks[$key] ?? null;

        if ($existingOwner !== null && $existingOwner->ownerId === $owner) {
            unset($this->locks[$key]);
        }
    }

    #[Override]
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
