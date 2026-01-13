<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Exceptions;

/**
 * Resolution Exception
 *
 * Thrown when a service cannot be resolved due to missing dependencies,
 * circular dependencies, or other resolution-time issues.
 *
 * @see     docs/Features/Core/Exceptions/ResolutionException.md
 */
class ResolutionException extends ContainerException {}
