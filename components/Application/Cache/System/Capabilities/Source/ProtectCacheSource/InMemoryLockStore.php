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
        private array $locks = [],
    ) {
    }

    #[Override]
    public function acquire(string $key, int $ttlSeconds = 30, ?string $owner = null): bool
    {
        $lockOwner = $owner ?? uniqid(more_entropy: true);

        if ($this->isAcquired(cacheKey: $key)) {
            $existingOwner = $this->locks[$key] ?? null;

            if ($existingOwner !== null && ! $existingOwner->isExpired(clock: $this->clock)) {
                return $owner !== null && $existingOwner->ownerId === $lockOwner;
            }
        }

        $this->locks[$key] = CacheLockOwner::current(
            ownerId   : $lockOwner,
            ttlSeconds: $ttlSeconds,
            clock     : $this->clock,
        );

        return true;
    }

    #[Override]
    public function isAcquired(string $key): bool
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
    public function release(string $key, ?string $owner = null): void
    {
        $existingOwner = $this->locks[$key] ?? null;

        if ($existingOwner !== null && ($owner === null || $existingOwner->ownerId === $owner)) {
            unset($this->locks[$key]);
        }
    }

    #[Override]
    public function getOwner(string $key): ?CacheLockOwner
    {
        if (! $this->isAcquired(cacheKey: $key)) {
            return null;
        }

        return $this->locks[$key];
    }

    public function releaseAll(): void
    {
        $this->locks = [];
    }
}
