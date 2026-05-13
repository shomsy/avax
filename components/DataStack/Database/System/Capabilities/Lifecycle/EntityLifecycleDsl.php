<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Lifecycle;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecycleRegistration;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\GlobalDatabaseLifecycleState;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleSource;

/**
 * Fluent DSL for registering entity lifecycle listeners.
 *
 * Usage:
 *   onEntity(User::class)
 *       ->creating(ValidateUser::class)
 *       ->created(EmitUserRegistered::class)
 *       ->updating(RecordUserAuditTrail::class);
 *
 * This is a declaration API only. No execution happens during registration.
 *
 * Registrations go into GlobalDatabaseLifecycleState — the same registry
 * used by EntityPersister at runtime.
 */
final class EntityLifecycleDsl
{
    public function __construct(
        private readonly string $entityClass,
    ) {
    }

    /**
     * Register a listener for the creating phase (before INSERT).
     */
    public function creating(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::Creating, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the created phase (after INSERT success).
     */
    public function created(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::Created, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the updating phase (before UPDATE).
     */
    public function updating(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::Updating, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the updated phase (after UPDATE success).
     */
    public function updated(string $listener, int $priority = 0): self
 {
        $this->register(EntityLifecyclePhase::Updated, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the saving phase (before INSERT or UPDATE).
     */
    public function saving(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::Saving, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the saved phase (after INSERT or UPDATE success).
     */
    public function saved(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::Saved, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the deleting phase (before DELETE).
     */
    public function deleting(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::Deleting, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the deleted phase (after DELETE success).
     */
    public function deleted(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::Deleted, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the restored phase (after soft-delete restore).
     */
    public function restored(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::Restored, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the failedToSave phase.
     */
    public function failedToSave(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::FailedToSave, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the failedToDelete phase.
     */
    public function failedToDelete(string $listener, int $priority = 0): self
    {
        $this->register(EntityLifecyclePhase::FailedToDelete, $listener, $priority);

        return $this;
    }

    private function register(EntityLifecyclePhase $phase, string $listener, int $priority): void
    {
        $registration = new EntityLifecycleRegistration(
            entityClass: $this->entityClass,
            phase: $phase,
            listener: $listener,
            priority: $priority,
            source: LifecycleSource::Dsl,
        );

        GlobalDatabaseLifecycleState::registry()->registerEntity($registration);
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
