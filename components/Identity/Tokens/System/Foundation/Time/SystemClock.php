<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Foundation\Time;

final readonly class SystemClock implements Clock
{
    public function now() : int
    {
        return time();
    }
}
