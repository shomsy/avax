<?php

declare(strict_types=1);

namespace components\Cache\System\Foundation\Time;

final class SystemClock implements Clock
{
    public function now() : Timestamp
    {
        return Timestamp::now();
    }
}