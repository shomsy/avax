<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Foundation;

final readonly class TaskDeadline
{
    public function __construct(
        public int $timestampMs,
    ) {}

    public static function fromSeconds(int $seconds) : self
    {
        return self::fromMilliseconds($seconds * 1000);
    }

    public static function fromMilliseconds(int $ms) : self
    {
        $now = (int) (microtime(true) * 1000);

        return new self(timestampMs: $now + $ms);
    }

    public function hasPassed() : bool
    {
        $now = (int) (microtime(true) * 1000);

        return $now > $this->timestampMs;
    }

    public function remainingSeconds() : float
    {
        return $this->remainingMs() / 1000.0;
    }

    public function remainingMs() : int
    {
        $now = (int) (microtime(true) * 1000);

        return max(0, $this->timestampMs - $now);
    }
}
