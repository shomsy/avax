<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Time;

final class SystemClock implements Clock
{
    public function now() : Timestamp
    {
        return Timestamp::now();
    }
}