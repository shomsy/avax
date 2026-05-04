<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Foundation\Time;

use DateTimeImmutable;

/**
 * Foundation clock for the container component.
 */
interface Clock
{
    public function now(): DateTimeImmutable;
    
    public function timestamp(): int;
}
