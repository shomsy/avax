<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Foundation\Time;

final class Clock
{
    public function now() : float
    {
        return microtime(as_float: true);
    }
}
