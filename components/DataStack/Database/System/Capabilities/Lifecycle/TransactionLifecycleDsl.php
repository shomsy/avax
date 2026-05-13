<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Lifecycle;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\GlobalDatabaseLifecycleState;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleSource;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecycleRegistration;

/**
 * Fluent DSL for registering transaction lifecycle listeners.
 *
 * Usage:
 *   onTransaction()
 *       ->committed(RecordTransactionAudit::class)
 *       ->afterCommit(PublishOutboxMessages::class)
 *       ->afterRollback(LogTransactionFailure::class);
 *
 * This is a declaration API only. No execution happens during registration.
 *
 * Registrations go into GlobalDatabaseLifecycleState — the same registry
 * used by Transaction at runtime.
 */
final class TransactionLifecycleDsl
{
    public function __construct()
    {
    }

    /**
     * Register a listener for the beginning phase (before BEGIN).
     */
    public function beginning(string $listener, int $priority = 0): self
    {
        $this->register(TransactionLifecyclePhase::Beginning, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the committed phase (after COMMIT SQL).
     */
    public function committed(string $listener, int $priority = 0): self
    {
        $this->register(TransactionLifecyclePhase::Committed, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the afterCommit phase (safe for external side effects).
     */
    public function afterCommit(string $listener, int $priority = 0): self
    {
        $this->register(TransactionLifecyclePhase::AfterCommit, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the rolledBack phase (after ROLLBACK SQL).
     */
    public function rolledBack(string $listener, int $priority = 0): self
    {
        $this->register(TransactionLifecyclePhase::RolledBack, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the afterRollback phase (cleanup after rollback).
     */
    public function afterRollback(string $listener, int $priority = 0): self
    {
        $this->register(TransactionLifecyclePhase::AfterRollback, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the failed phase.
     */
    public function failed(string $listener, int $priority = 0): self
    {
        $this->register(TransactionLifecyclePhase::Failed, $listener, $priority);

        return $this;
    }

    private function register(TransactionLifecyclePhase $phase, string $listener, int $priority): void
    {
        $registration = new TransactionLifecycleRegistration(
            phase: $phase,
            listener: $listener,
            priority: $priority,
            source: LifecycleSource::Dsl,
        );

        GlobalDatabaseLifecycleState::registry()->registerTransaction($registration);
    }

    /**
     * Set the shared registry instance (used by boot-time compilation).
     */
    public static function setRegistry(CompiledDatabaseLifecycleRegistry $registry): void
    {
        GlobalDatabaseLifecycleState::setRegistry($registry);
    }

    /**
     * Get the current registry instance.
     */
    public static function getRegistry(): CompiledDatabaseLifecycleRegistry
    {
        return GlobalDatabaseLifecycleState::registry();
    }

    /**
     * Reset the registry (used for testing).
     */
    public static function reset(): void
    {
        GlobalDatabaseLifecycleState::reset();
    }
}
