<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Time;

use DateInterval;
use DateTimeImmutable;

final readonly class Expiry
{
    public function __construct(public DateTimeImmutable $at) {}

    public static function after(DateInterval $dateInterval, ?Clock $clock = null) : self
    {
        $clock ??= new SystemClock();

        return new self(at: $clock->now()->add(interval: $dateInterval));
    }

    public function isExpired(?DateTimeImmutable $now = null) : bool
    {
        $now ??= new DateTimeImmutable();

        return $now >= $this->at;
    }
}
