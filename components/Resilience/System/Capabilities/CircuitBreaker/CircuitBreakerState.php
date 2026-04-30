<?php

declare(strict_types=1);

namespace Avax\Components\Resilience\System\Capabilities\CircuitBreaker;

enum CircuitBreakerState: string
{
    case Closed   = 'closed';
    case Open     = 'open';
    case HalfOpen = 'half_open';
}
