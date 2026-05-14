<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Throwable;

final class ResetApplicationState
{
    private array $resettableStates = [];

    private array $resetCallbacks = [];

    private array $activeTransactions = [];

    private bool $transactionLeakDetection = true;

    public function __construct(
        private readonly StateResetRegistry $stateResetRegistry,
    ) {}

    public function registerResettable(string $name, ResettableState $resettableState) : self
    {
        $this->resettableStates[$name] = $resettableState;
        $this->stateResetRegistry->register($name, $resettableState);

        return $this;
    }

    public function onReset(string $name, callable $callback) : self
    {
        $this->resetCallbacks[$name] = $callback;

        return $this;
    }

    public function setTransactionLeakDetection(bool $enabled) : self
    {
        $this->transactionLeakDetection = $enabled;

        return $this;
    }

    public function trackTransaction(string $connectionName) : void
    {
        $this->activeTransactions[$connectionName] = $connectionName;
    }

    public function reset() : array
    {
        $reset    = [];
        $failures = [];

        $stateResetReport = $this->stateResetRegistry->resetAll();
        $reset            = $stateResetReport->resetComponents();

        foreach ($stateResetReport->failures() as $name => $throwable) {
            $failures[$name] = $throwable->getMessage();
        }

        foreach ($this->resetCallbacks as $name => $callback) {
            try {
                $callback();
                $reset[] = $name;
            } catch (Throwable $throwable) {
                $failures[$name] = $throwable->getMessage();
            }
        }

        $transactionLeaks = $this->detectTransactionLeaks();

        foreach ($transactionLeaks as $transactionLeak) {
            $this->completeTransaction($transactionLeak);
        }

        return [
            'reset'            => $reset,
            'failures'         => $failures,
            'transactionLeaks' => $transactionLeaks,
        ];
    }

    public function detectTransactionLeaks() : array
    {
        if (! $this->transactionLeakDetection) {
            return [];
        }

        return array_values($this->activeTransactions);
    }

    public function completeTransaction(string $connectionName) : void
    {
        unset($this->activeTransactions[$connectionName]);
    }

    public function transactionLeakCount() : int
    {
        return count($this->activeTransactions);
    }

    public function hasTransactionLeaks() : bool
    {
        return $this->activeTransactions !== [];
    }

    public function clearTransactions() : void
    {
        $this->activeTransactions = [];
    }
}
