<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\Foundation;

final class Clock
{
    public function now() : int
    {
        return time();
    }
}
