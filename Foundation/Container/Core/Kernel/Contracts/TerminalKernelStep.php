<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Contracts;

/**
 * Terminal Kernel Step
 *
 * A marker interface for steps that can potentially terminate the resolution
 * pipeline early (e.g., by finding an instance in cache/scope).
 *
 * @see docs/Core/Kernel/Contracts/TerminalKernelStep.md#quick-summary
 */
interface TerminalKernelStep extends KernelStep {}
