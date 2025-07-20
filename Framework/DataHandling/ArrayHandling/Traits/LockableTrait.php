<?php

declare(strict_types=1);

namespace Gemini\DataHandling\ArrayHandling\Traits;

use LogicException;

/**
 * Trait LockableTrait
 *
 * Provides a locking mechanism to enforce immutability on collection-like objects.
 * Intended for use in data container classes (e.g., value object arrays) where mutation
 * should be explicitly prohibited after initialization or transformation.
 *
 * When locked, any attempt to mutate the object via methods like `set`, `forget`, `add`,
 * or internal `setItems` should result in a runtime exception.
 *
 * This trait enforces strict runtime safety in contexts such as:
 * - Domain-driven value object snapshots
 * - Immutable API response structures
 * - Secure, read-only configuration holders
 *
 * @package Gemini\DataHandling\ArrayHandling\Traits
 */
trait LockableTrait
{
    /**
     * Indicates whether the current instance is locked and protected from mutation.
     */
    protected bool $locked = false;

    /**
     * Locks the current instance, making all mutating operations forbidden.
     *
     * This method should be called once the object reaches a stable state, typically
     * after construction, transformation, or hydration from a DTO.
     *
     * @return static Returns the same instance for fluent chaining.
     */
    public function lock() : static
    {
        // Enables the immutability flag
        $this->locked = true;

        // Return self to support fluent calls
        return $this;
    }

    /**
     * Indicates whether this instance has been locked.
     *
     * Useful for consumers to check immutability status.
     *
     * @return bool True if locked, false otherwise.
     */
    public function isLocked() : bool
    {
        return $this->locked;
    }

    /**
     * Checks whether mutation is allowed. Throws an exception if the instance is locked.
     *
     * Should be called at the top of any mutating method (e.g., set, forget, etc.)
     * to enforce write protection contract.
     *
     * @throws LogicException If mutation is attempted on a locked instance.
     */
    protected function assertNotLocked() : void
    {
        // Enforces immutability post-lock
        if ($this->locked === true) {
            throw new LogicException(message: 'Mutation is forbidden: this instance is locked and read-only.');
        }
    }
}
