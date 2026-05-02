<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\BreakCircuit;

use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreaker;

final readonly class BreakCircuit
{
    public function breaker(int $failureThreshold = 3, int $cooldownSeconds = 30): CircuitBreaker
    {
        return new CircuitBreaker(
            failureThreshold: $failureThreshold,
            cooldownSeconds : $cooldownSeconds,
        );
    }
}
