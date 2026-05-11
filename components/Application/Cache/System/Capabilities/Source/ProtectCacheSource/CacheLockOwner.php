<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Override;
use Stringable;

final readonly class CacheLockOwner implements Stringable
{
    public function __construct(
        public string $ownerId,
        public int $acquiredAt,
        public int $ttlSeconds,
    ) {
    }

    public static function current(string $ownerId, int $ttlSeconds = 30, Clock|null $clock = null) : self
    {
        $clock ??= new SystemClock();
        $now = $clock->now();

        return new self(
            ownerId   : $ownerId,
            acquiredAt: $now->seconds,
            ttlSeconds: $ttlSeconds,
        );
    }

    public function isExpired(Clock|null $clock = null) : bool
    {
        $clock ??= new SystemClock();
        $now = $clock->now();

        return ($now->seconds - $this->acquiredAt) > $this->ttlSeconds;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->ownerId;
    }
}
