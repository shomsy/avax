<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

/**
 * Interface for consistency policies.
 *
 * Defines the contract for different consistency strategies
 * used in distributed data systems.
 */
interface ConsistencyPolicy
{
    /**
     * Returns the name of this consistency policy.
     */
    public function name(): string;

    /**
     * Determines if a read is allowed given the current state.
     *
     * @param  mixed  $currentValue  The current value
     * @param  mixed  $pendingWrite  Any pending write
     */
    public function canRead(mixed $currentValue, mixed $pendingWrite = null): bool;

    /**
     * Determines if a write should be accepted.
     *
     * @param  mixed  $currentValue  The current value
     * @param  mixed  $newValue  The proposed new value
     */
    public function canWrite(mixed $currentValue, mixed $newValue): bool;

    /**
     * Resolves a conflict between two values.
     *
     * @param  mixed  $valueA  First conflicting value
     * @param  mixed  $valueB  Second conflicting value
     * @param  array<string, mixed>  $context  Additional context for resolution
     * @return mixed The resolved value
     */
    public function resolveConflict(mixed $valueA, mixed $valueB, array $context = []): mixed;

    /**
     * Returns a description of this policy's guarantees.
     */
    public function description(): string;

    /**
     * Returns the consistency level (0 = none, 1 = strong).
     */
    public function consistencyLevel(): float;
}
