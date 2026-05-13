<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\ResolveCallable;

use RuntimeException;

/**
 * Thrown when a class-string or callable cannot be resolved.
 */
class CallableResolutionFailed extends RuntimeException
{
}
