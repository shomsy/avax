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
    public function record(IdempotencyKey $key, mixed $result) : void
    {
        $this->executed[$key->toString()] = $result;
    }

    /**
     * Check if a key has already been executed.
     */
    public function hasExecuted(IdempotencyKey $key) : bool
    {
        return isset($this->executed[$key->toString()]);
    }

    /**
     * Get the cached result for an already-executed key.
     */
    public function getResult(IdempotencyKey $key) : mixed
    {
        return $this->executed[$key->toString()] ?? null;
    }

    /**
     * Clear all recorded keys (useful for testing).
     */
    public function clear() : void
    {
        $this->executed = [];
    }
}
