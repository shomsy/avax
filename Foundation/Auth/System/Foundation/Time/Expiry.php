<?php

declare(strict_types=1);

namespace Avax\Auth\System\Foundation\Time;

use DateInterval;
use DateTimeImmutable;

final readonly class Expiry
{
    public DateTimeImmutable $at;

    public function __construct(DateTimeImmutable $at)
    {
        $this->at = $at;
    }

    public static function after(DateInterval $interval, ?Clock $clock = null) : self
    {
        $clock ??= new SystemClock();

        return new self($clock->now()->add($interval));
    }

    public function isExpired(?DateTimeImmutable $now = null) : bool
    {
        $now ??= new DateTimeImmutable();

        return $now >= $this->at;
    }
}
