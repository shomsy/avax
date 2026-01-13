<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Exceptions;

use Psr\Container\ContainerExceptionInterface as PsrContainerExceptionInterface;
use Throwable;

/**
 * Container Exception Interface
 *
 * Marker interface for all container exceptions.
 * Extends PSR-11 ContainerExceptionInterface for compatibility.
 *
 * @see     docs/Features/Core/Exceptions/ContainerExceptionInterface.md
 */
interface ContainerExceptionInterface extends PsrContainerExceptionInterface, Throwable {}
