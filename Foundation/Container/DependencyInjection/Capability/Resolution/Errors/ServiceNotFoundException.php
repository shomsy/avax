<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Errors;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Service Not Found Exception
 *
 * Thrown when a requested service identifier is not registered in the container
 * and cannot be auto-wired.
 *
 */
class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface {}
