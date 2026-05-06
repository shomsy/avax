<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

/**
 * Result of a conflict resolution operation.
 */
final readonly class ConflictResolutionResult
{
    public function __construct(
        public mixed $resolvedValue,
        public string $strategy,
        public bool $wasConflict,
        public array $details = [],
    ) {
    }

    /**
     * Creates a result with no conflict (straightforward value).
     */
    public static function noConflict(mixed $value): self
    {
        return new self(
            resolvedValue: $value,
            strategy: 'no_conflict',
            wasConflict: false,
        );
    }

    /**
     * Creates a result from a conflict resolution.
     */
    public static function resolved(mixed $value, string $strategy, array $details = []): self
    {
        return new self(
            resolvedValue: $value,
            strategy: $strategy,
            wasConflict: true,
            details: $details,
        );
    }
}
