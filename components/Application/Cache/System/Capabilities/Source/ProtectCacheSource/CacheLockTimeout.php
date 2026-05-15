<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final readonly class CacheLockTimeout
{
    public function __construct(public int $seconds = 5)
    {
    }

    public function isExpired(int $acquiredAt, Clock $clock) : bool
    {
        $now = $clock->now();

        return ($now->seconds - $acquiredAt) > $this->seconds;
    }

    public function inMilliseconds(): int
    {
        return $this->seconds * 1000;
    }
}
