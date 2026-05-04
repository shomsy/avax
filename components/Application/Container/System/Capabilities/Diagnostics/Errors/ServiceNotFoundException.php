<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Explicit PSR-11 not-found boundary for missing services.
 */
final class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
