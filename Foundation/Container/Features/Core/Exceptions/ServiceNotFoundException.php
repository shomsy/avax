<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Service Not Found Exception
 *
 * Thrown when a requested service identifier is not registered in the container
 * and cannot be auto-wired.
 *
 * @see     docs/Features/Core/Exceptions/ServiceNotFoundException.md
 */
class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface {}
