<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\WarmApplication;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;

/**
 * FlushScopedInstances — Flushes all scoped container instances and resets the request scope.
 *
 * Integrates with StateResetRegistry to reset all registered resettable states,
 * then closes and reopens the request scope to clear per-request data.
 */
final class FlushScopedInstances
{
    private RequestScope|null $scope = null;

    private StateResetRegistry|null $resetRegistry = null;

    /**
     * @var list<callable(): void>
     */
    private array $flushCallbacks = [];

    public function setRequestScope(RequestScope $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function setResetRegistry(StateResetRegistry $registry): self
    {
        $this->resetRegistry = $registry;

        return $this;
    }

    public function addFlushCallback(callable $callback): self
    {
        $this->flushCallbacks[] = $callback;

        return $this;
    }

    /**
     * Flush all scoped instances and reset state.
     * Returns the reset report from the StateResetRegistry if available.
     */
    public function flush() : StateResetReport|null
    {
        $report = null;

        // Run registered state resets
        if ($this->resetRegistry !== null) {
            $report = $this->resetRegistry->resetAll();
        }

        // Run custom flush callbacks
        foreach ($this->flushCallbacks as $callback) {
            $callback();
        }

        // Close and reopen the request scope to clear per-request data
        if ($this->scope !== null && $this->scope->isOpen()) {
            $this->scope->close();
        }
        if ($this->scope !== null && ! $this->scope->isOpen()) {
            $this->scope->open();
        }

        return $report;
    }
}
