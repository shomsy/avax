<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Time;

use DateTimeImmutable;

/**
 * ClockInterface — contract for time abstraction in Identity flows.
 *
 * Adapted from the enterprise reference package.
 * The concrete Clock class in Auth/System/Foundation/Clock.php implements this contract.
 */
interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
