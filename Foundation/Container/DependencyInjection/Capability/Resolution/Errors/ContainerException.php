<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Errors;

use RuntimeException;

/**
 * Container Exception
 *
 * Base exception for all container-related errors.
 * Thrown when container operations fail due to configuration issues,
 * resolution failures, or other runtime problems.
 *
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface {}
