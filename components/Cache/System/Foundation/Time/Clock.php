<?php

declare(strict_types=1);

namespace components\Cache\System\Foundation\Time;

interface Clock
{
    public function now() : Timestamp;
}