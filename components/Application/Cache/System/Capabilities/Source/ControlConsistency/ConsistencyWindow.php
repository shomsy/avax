<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ControlConsistency;

readonly class ConsistencyWindow
{
    public function __construct(
        public int $readWindowMs = 1000,
        public int $writeWindowMs = 500,
        public int $propagationDelayMs = 100,
    ) {}

    public static function relaxed() : self
    {
        return new self(readWindowMs: 5000, writeWindowMs: 2000, propagationDelayMs: 500);
    }

    public static function strict() : self
    {
        return new self(readWindowMs: 100, writeWindowMs: 50, propagationDelayMs: 10);
    }

    public function totalMaxDelayMs() : int
    {
        return $this->readWindowMs + $this->writeWindowMs + $this->propagationDelayMs;
    }
}
