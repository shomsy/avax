<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

use RuntimeException;

/**
 * Single canonical compiled registry for all database lifecycle listeners.
 *
 * DSL registrations + attribute scans + configuration compile into this registry.
 * The registry is frozen at boot-time after compilation.
 * Runtime reads the compiled registry — no reflection in the hot path.
 */
final class CompiledDatabaseLifecycleRegistry
{
    /**
     * Entity listeners: entityClass -> phase -> sorted list of [listener, priority, source, mode, thresholdMs?].
     *
     * @var array<string, array<string, list<array{listener: string, priority: int, source: LifecycleSource, mode: LifecycleExecutionMode, thresholdMs: ?int}>>>
     */
    private array $entityListeners = [];

    /**
     * Query listeners: phase -> sorted list.
     *
     * @var array<string, list<array{listener: string, priority: int, source: LifecycleSource, mode: LifecycleExecutionMode, thresholdMs: ?int}>>
     */
    private array $queryListeners = [];

    /**
     * Transaction listeners: phase -> sorted list.
     *
     * @var array<string, list<array{listener: string, priority: int, source: LifecycleSource, mode: LifecycleExecutionMode, thresholdMs: ?int}>>
     */
    private array $transactionListeners = [];

    private bool $frozen = false;

    /**
     * Register an entity lifecycle listener.
     *
     * @throws RuntimeException if the registry is frozen.
     */
    public function registerEntity(EntityLifecycleRegistration $registration): void
    {
        $this->assertNotFrozen();

        $entityClass = $registration->entityClass;
        $phase = $registration->phase->value;

        $this->entityListeners[$entityClass][$phase][] = [
            'listener' => $registration->listener,
            'priority' => $registration->priority,
            'source' => $registration->source,
            'mode' => $registration->mode,
            'thresholdMs' => null,
        ];

        // Sort: priority descending, registration order tie-break (natural array order).
        usort(
            $this->entityListeners[$entityClass][$phase],
            static fn (array $a, array $b): int => $b['priority'] <=> $a['priority'],
        );
    }

    /**
     * Register a query lifecycle listener.
     *
     * @throws RuntimeException if the registry is frozen.
     */
    public function registerQuery(QueryLifecycleRegistration $registration): void
    {
        $this->assertNotFrozen();

        $phase = $registration->phase->value;

        $this->queryListeners[$phase][] = [
            'listener' => $registration->listener,
            'priority' => $registration->priority,
            'source' => $registration->source,
            'mode' => $registration->mode,
            'thresholdMs' => $registration->thresholdMs,
        ];

        usort(
            $this->queryListeners[$phase],
            static fn (array $a, array $b): int => $b['priority'] <=> $a['priority'],
        );
    }

    /**
     * Register a transaction lifecycle listener.
     *
     * @throws RuntimeException if the registry is frozen.
     */
    public function registerTransaction(TransactionLifecycleRegistration $registration): void
    {
        $this->assertNotFrozen();

        $phase = $registration->phase->value;

        $this->transactionListeners[$phase][] = [
            'listener' => $registration->listener,
            'priority' => $registration->priority,
            'source' => $registration->source,
            'mode' => $registration->mode,
            'thresholdMs' => null,
        ];

        usort(
            $this->transactionListeners[$phase],
            static fn (array $a, array $b): int => $b['priority'] <=> $a['priority'],
        );
    }

    /**
     * Resolve entity lifecycle listeners for a specific entity and phase.
     *
     * Exact lookup only — no superset expansion.
     * Superset dispatch (saving includes creating/updating, saved includes created/updated)
     * is handled by EntityPersister which explicitly dispatches both phases.
     *
     * @return list<array{listener: string, priority: int, source: LifecycleSource, mode: LifecycleExecutionMode, thresholdMs: ?int}>
     */
    public function entityListenersFor(string $entityClass, EntityLifecyclePhase $phase): array
    {
        return $this->entityListeners[$entityClass][$phase->value] ?? [];
    }

    /**
     * Resolve query lifecycle listeners for a phase.
     *
     * @return list<array{listener: string, priority: int, source: LifecycleSource, mode: LifecycleExecutionMode, thresholdMs: ?int}>
     */
    public function queryListenersFor(QueryLifecyclePhase $phase): array
    {
        return $this->queryListeners[$phase->value] ?? [];
    }

    /**
     * Resolve transaction lifecycle listeners for a phase.
     *
     * @return list<array{listener: string, priority: int, source: LifecycleSource, mode: LifecycleExecutionMode, thresholdMs: ?int}>
     */
    public function transactionListenersFor(TransactionLifecyclePhase $phase): array
    {
        return $this->transactionListeners[$phase->value] ?? [];
    }

    /**
     * Freeze the registry — no further registrations allowed.
     */
    public function freeze(): void
    {
        $this->frozen = true;
    }

    /**
     * Check if the registry is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    /**
     * Get all registered entity classes (for compile-time boot).
     *
     * @return list<string>
     */
    public function getRegisteredEntityClasses(): array
    {
        return array_keys($this->entityListeners);
    }

    /**
     * Get all events with listeners (for compile-time extraction).
     *
     * @return list<string>
     */
    public function getAllLifecycleKeys(): array
    {
        $keys = [];

        foreach ($this->entityListeners as $entityClass => $phases) {
            foreach ($phases as $phaseName => $_listeners) {
                $keys[] = "entity:{$entityClass}:{$phaseName}";
            }
        }

        foreach ($this->queryListeners as $phase => $listeners) {
            if ($listeners !== []) {
                $keys[] = "query:{$phase}";
            }
        }

        foreach ($this->transactionListeners as $phase => $listeners) {
            if ($listeners !== []) {
                $keys[] = "transaction:{$phase}";
            }
        }

        return $keys;
    }

    private function assertNotFrozen(): void
    {
        if ($this->frozen) {
            throw new RuntimeException(
                'Cannot register lifecycle listeners: the compiled registry is frozen.',
            );
        }
    }
}
