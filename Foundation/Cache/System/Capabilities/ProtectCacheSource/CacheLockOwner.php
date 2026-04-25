<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCacheSource;

use Stringable;

final readonly class CacheLockOwner implements Stringable
{
    public function __construct(
        public string $ownerId,
        public int    $acquiredAt,
        public int    $ttlSeconds
    ) {}

    public static function current(string $ownerId, int $ttlSeconds = 30) : self
    {
        return new self(
            ownerId   : $ownerId,
            acquiredAt: time(),
            ttlSeconds: $ttlSeconds
        );
    }

    public function isExpired() : bool
    {
        return (time() - $this->acquiredAt) > $this->ttlSeconds;
    }

    public function __toString() : string
    {
        return $this->ownerId;
    }
}