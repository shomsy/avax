<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Errors;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Base explicit failure boundary for container operations.
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
