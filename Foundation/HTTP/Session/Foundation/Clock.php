<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Foundation;

final class Clock
{
    public function now() : int
    {
        return time();
    }
}
