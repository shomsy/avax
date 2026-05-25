<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Time;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
