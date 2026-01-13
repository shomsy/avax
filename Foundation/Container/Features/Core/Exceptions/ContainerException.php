<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Exceptions;

use RuntimeException;

/**
 * Container Exception
 *
 * Base exception for all container-related errors.
 * Thrown when container operations fail due to configuration issues,
 * resolution failures, or other runtime problems.
 *
 * @see     docs/Features/Core/Exceptions/ContainerException.md
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface {}
