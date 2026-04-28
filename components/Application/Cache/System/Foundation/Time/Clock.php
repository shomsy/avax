<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Time;

interface Clock
{
    public function now() : Timestamp;
}