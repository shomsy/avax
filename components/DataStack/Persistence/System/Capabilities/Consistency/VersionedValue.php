<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

/**
 * A versioned value with vector clock for conflict detection.
 */
final readonly class VersionedValue
{
    public function __construct(
        public mixed $value,
        public VectorClock $clock,
        public string $nodeId,
        public float $timestamp,
    ) {
    }

    /**
     * Creates a new versioned value.
     */
    public static function create(
        mixed $value,
        string $nodeId,
        ?VectorClock $vectorClock = null,
        ?float $timestamp = null,
    ): self {
        $vectorClock ??= VectorClock::initial($nodeId);
        $timestamp ??= microtime(true);

        return new self(
            value: $value,
            clock: $vectorClock,
            nodeId: $nodeId,
            timestamp: $timestamp,
        );
    }

    /**
     * Creates an updated version of this value.
     */
    public function update(mixed $newValue): self
    {
        return new self(
            value: $newValue,
            clock: $this->clock->increment($this->nodeId),
            nodeId: $this->nodeId,
            timestamp: microtime(true),
        );
    }
}
