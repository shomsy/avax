<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency;

/**
 * Simple in-memory store for tracking executed idempotency keys.
 */
final class IdempotencyStore
{
    /**
     * @var array<string, mixed>
     */
    private array $executed = [];

    /**
     * Record that a key has been executed with a result.
     */
    public function record(IdempotencyKey $idempotencyKey, mixed $result) : void
    {
        $this->executed[$idempotencyKey->toString()] = $result;
    }

    /**
     * Check if a key has already been executed.
     */
    public function hasExecuted(IdempotencyKey $idempotencyKey) : bool
    {
        return isset($this->executed[$idempotencyKey->toString()]);
    }

    /**
     * Get the cached result for an already-executed key.
     */
    public function getResult(IdempotencyKey $idempotencyKey) : mixed
    {
        return $this->executed[$idempotencyKey->toString()] ?? null;
    }

    /**
     * Clear all recorded keys (useful for testing).
     */
    public function clear(): void
    {
        $this->executed = [];
    }
}
