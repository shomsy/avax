<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Resolution\Pipeline\Contracts;

/**
 * Terminal Kernel Step
 *
 * A marker interface for steps that can potentially terminate the resolution
 * pipeline early (e.g., by finding an instance in cache/scope).
 *
 */
interface TerminalKernelStep extends KernelStep {}
