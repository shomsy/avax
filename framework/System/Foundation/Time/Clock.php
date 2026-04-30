<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Time;

use DateTimeImmutable;

interface Clock
{
    public function now() : DateTimeImmutable;
}
