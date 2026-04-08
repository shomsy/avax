<?php

declare(strict_types=1);

namespace Avax\Container\Errors;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Base explicit failure boundary for container operations.
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
