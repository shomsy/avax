<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Errors;

/**
 * Resolution Exception
 *
 * Thrown when a service cannot be resolved due to missing dependencies,
 * circular dependencies, or other resolution-time issues.
 *
 */
class ResolutionException extends ContainerException {}
