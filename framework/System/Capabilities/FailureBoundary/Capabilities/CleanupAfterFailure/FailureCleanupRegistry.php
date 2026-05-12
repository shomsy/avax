<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Closure;
use Throwable;

/**
 * FailureCleanupRegistry — Registry for cleanup hooks that run after protected action execution.
 *
 * Cleanup hooks are invoked on every execution path:
 * - success
 * - mapped failure
 * - unmapped failure (rethrown)
 * - reporter failure
 * - fallback failure
 *
 * Hooks receive the FailureContext and must not throw.
 * If a cleanup hook throws, the exception is captured but does not
 * hide the original failure unless the policy explicitly requires it.
 */
final class FailureCleanupRegistry
{
    /**
     * @var list<Closure(FailureContext): void>
     */
    private array $hooks = [];

    /**
     * Register a cleanup hook.
     *
     * @param Closure(FailureContext): void $hook
     */
    public function register(Closure $hook) : void
    {
        $this->hooks[] = $hook;
    }

    /**
     * Execute all registered cleanup hooks.
     *
     * Hooks are executed in registration order.
     * Individual hook failures are captured and do not prevent subsequent hooks from running.
     */
    public function cleanup(FailureContext $context) : void
    {
        foreach ($this->hooks as $hook) {
            try {
                $hook($context);
            } catch (Throwable) {
                // Cleanup hook failure is captured but does not prevent
                // subsequent hooks from running or hide the original failure.
                // In production, this should be logged via observability.
            }
        }
    }

    /**
     * Clear all registered hooks.
     */
    public function clear() : void
    {
        $this->hooks = [];
    }

    /**
     * Return the number of registered hooks.
     */
    public function count() : int
    {
        return count($this->hooks);
    }
}
