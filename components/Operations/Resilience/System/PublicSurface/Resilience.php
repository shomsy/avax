<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\PublicSurface;

use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreaker;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryBuilder;
use Closure;

final readonly class Resilience
{
    public static function retry(Closure $operation) : RetryBuilder
    {
        return new RetryBuilder($operation);
    }

    public static function circuitBreaker(int $failureThreshold = 3, int $cooldownSeconds = 30) : CircuitBreaker
    {
        return new CircuitBreaker(
            failureThreshold: $failureThreshold,
            cooldownSeconds : $cooldownSeconds,
        );
    }
}
