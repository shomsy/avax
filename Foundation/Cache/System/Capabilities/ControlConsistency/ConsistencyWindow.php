<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ControlConsistency;

readonly class ConsistencyWindow
{
    public function __construct(
        public int $readWindowMs = 1000,
        public int $writeWindowMs = 500,
        public int $propagationDelayMs = 100
    ) {}

    public static function relaxed() : self
    {
        return new self(5000, 2000, 500);
    }

    public static function strict() : self
    {
        return new self(100, 50, 10);
    }

    public function totalMaxDelayMs() : int
    {
        return $this->readWindowMs + $this->writeWindowMs + $this->propagationDelayMs;
    }
}