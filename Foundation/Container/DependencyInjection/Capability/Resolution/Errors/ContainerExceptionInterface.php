<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Errors;

use Psr\Container\ContainerExceptionInterface as PsrContainerExceptionInterface;
use Throwable;

/**
 * Container Exception Interface
 *
 * Marker interface for all container exceptions.
 * Extends PSR-11 ContainerExceptionInterface for compatibility.
 *
 */
interface ContainerExceptionInterface extends PsrContainerExceptionInterface, Throwable {}
