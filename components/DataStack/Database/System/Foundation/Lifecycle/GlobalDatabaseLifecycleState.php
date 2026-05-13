<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

use RuntimeException;

/**
 * Global boot-time database lifecycle registry.
 *
 * Single shared registry used by:
 * - EntityLifecycleDsl (onEntity)
 * - QueryLifecycleDsl (onQuery)
 * - TransactionLifecycleDsl (onTransaction)
 * - EntityPersister (default registry)
 * - QueryOrchestrator (default registry)
 * - Transaction (default registry)
 *
 * This ensures public DSL registrations reach the same runtime that dispatches events.
 *
 * Boot sequence:
 * 1. DSL classes register listeners into GlobalDatabaseLifecycleState::registry()
 * 2. At boot-end, GlobalDatabaseLifecycleState::freeze() is called
 * 3. Runtime classes read the frozen compiled registry
 *
 * Testing: GlobalDatabaseLifecycleState::reset() clears all state between tests.
 */
final class GlobalDatabaseLifecycleState
{
    private static ?CompiledDatabaseLifecycleRegistry $registry = null;
    private static bool                               $frozen   = false;

    /**
     * Get the shared lifecycle registry.
     *
     * Creates a fresh registry on first call.
     * Returns the same instance on subsequent calls.
     */
    public static function registry() : CompiledDatabaseLifecycleRegistry
    {
        if (self::$registry === null) {
            self::$registry = new CompiledDatabaseLifecycleRegistry();
        }

        return self::$registry;
    }

    /**
     * Replace the registry (used by compile-time boot).
     *
     * @throws RuntimeException if already frozen
     */
    public static function setRegistry(CompiledDatabaseLifecycleRegistry $registry) : void
    {
        self::assertNotFrozen();
        self::$registry = $registry;
        self::$frozen   = false;
    }

    private static function assertNotFrozen() : void
    {
        if (self::$frozen) {
            throw new RuntimeException(
                'Cannot modify global database lifecycle state: the registry is frozen.',
            );
        }
    }

    /**
     * Freeze the registry — no further registrations allowed.
     */
    public static function freeze() : void
    {
        self::$frozen = true;
        self::$registry?->freeze();
    }

    /**
     * Check if the registry is frozen.
     */
    public static function isFrozen() : bool
    {
        return self::$frozen;
    }

    /**
     * Reset all global lifecycle state (testing only).
     */
    public static function reset() : void
    {
        self::$registry = null;
        self::$frozen   = false;
    }
}
