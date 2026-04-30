<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final readonly class CacheLockTimeout
{
    public function __construct(public int $seconds = 5) {}

    public function isExpired(int $acquiredAt, Clock|null $clock = null) : bool
    {
        $clock ??= new SystemClock();
        $now = $clock->now();

        return ($now->seconds - $acquiredAt) > $this->seconds;
    }

    public function inMilliseconds() : int
    {
        return $this->seconds * 1000;
    }
}
