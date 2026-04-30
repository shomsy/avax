<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Time;

use DateTimeImmutable;

final readonly class Timestamp
{
    public function __construct(
        private DateTimeImmutable $value,
    ) {
    }

    public static function now(Clock $clock) : self
    {
        return new self(value: $clock->now());
    }

    public function toDateTimeImmutable() : DateTimeImmutable
    {
        return $this->value;
    }

    public function toString() : string
    {
        return $this->value->format(format: 'Y-m-d H:i:s');
    }

    public function isBefore(Timestamp $other) : bool
    {
        return $this->value < $other->value;
    }

    public function isAfter(Timestamp $other) : bool
    {
        return $this->value > $other->value;
    }
}
