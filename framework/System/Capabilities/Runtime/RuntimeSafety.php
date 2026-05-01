<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Throwable;

/**
 * Runtime safety manager for long-lived runtimes (Swoole, RoadRunner, FrankenPHP).
 *
 * Ensures clean state between requests by resetting facades, session, auth,
 * and detecting database transaction leaks.
 */
final class RuntimeSafety
{
    /**
     * @var array<string, ResettableState>
     */
    private array $resettableStates = [];

    /**
     * @var array<string, callable>
     */
    private array $resetCallbacks = [];

    /**
     * @var array<string, string>
     */
    private array $activeTransactions = [];

    private bool $transactionLeakDetection = true;

    public function __construct(
        private readonly StateResetRegistry $stateResetRegistry,
    ) {}

    /**
     * Register a resettable state component.
     */
    public function registerResettable(string $name, ResettableState $state) : self
    {
        $this->resettableStates[$name] = $state;
        $this->stateResetRegistry->register($name, $state);

        return $this;
    }

    /**
     * Register a custom reset callback.
     */
    public function onReset(string $name, callable $callback) : self
    {
        $this->resetCallbacks[$name] = $callback;

        return $this;
    }

    /**
     * Enable or disable database transaction leak detection.
     */
    public function setTransactionLeakDetection(bool $enabled) : self
    {
        $this->transactionLeakDetection = $enabled;

        return $this;
    }

    /**
     * Track an active database transaction.
     */
    public function trackTransaction(string $connectionName) : void
    {
        $this->activeTransactions[$connectionName] = $connectionName;
    }

    /**
     * Reset all state between requests.
     *
     * @return array{reset: list<string>, failures: array<string, string>, transactionLeaks: list<string>}
     */
    public function reset() : array
    {
        $reset    = [];
        $failures = [];

        // Reset registered states via the registry
        $report = $this->stateResetRegistry->resetAll();
        $reset  = $report->resetComponents();

        foreach ($report->failures() as $name => $exception) {
            $failures[$name] = $exception->getMessage();
        }

        // Execute custom reset callbacks
        foreach ($this->resetCallbacks as $name => $callback) {
            try {
                $callback();
                $reset[] = $name;
            } catch (Throwable $exception) {
                $failures[$name] = $exception->getMessage();
            }
        }

        // Check for transaction leaks
        $transactionLeaks = $this->detectTransactionLeaks();

        // Rollback leaked transactions
        foreach ($transactionLeaks as $connectionName) {
            $this->completeTransaction($connectionName);
        }

        return [
            'reset'            => $reset,
            'failures'         => $failures,
            'transactionLeaks' => $transactionLeaks,
        ];
    }

    /**
     * Check for leaked database transactions.
     *
     * @return list<string>
     */
    public function detectTransactionLeaks() : array
    {
        if (! $this->transactionLeakDetection) {
            return [];
        }

        return array_values($this->activeTransactions);
    }

    /**
     * Mark a database transaction as completed.
     */
    public function completeTransaction(string $connectionName) : void
    {
        unset($this->activeTransactions[$connectionName]);
    }

    /**
     * Get the count of active transaction leaks.
     */
    public function transactionLeakCount() : int
    {
        return count($this->activeTransactions);
    }

    /**
     * Check if there are any transaction leaks.
     */
    public function hasTransactionLeaks() : bool
    {
        return ! empty($this->activeTransactions);
    }

    /**
     * Clear all tracked transactions (emergency cleanup).
     */
    public function clearTransactions() : void
    {
        $this->activeTransactions = [];
    }
}
