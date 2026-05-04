<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Failure\System\PublicSurface;

enum FailureMode: string
{
    case TIMEOUT = 'timeout';
    case CIRCUIT_OPEN = 'circuit-open';
    case RATE_LIMIT = 'rate-limit';
    case PARTITION = 'partition';
    case CORRUPTION = 'corruption';
    case DUPLICATE = 'duplicate';
    case REORDERING = 'reordering';
}
