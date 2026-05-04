<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

/**
 * A detected conflict between concurrent writes.
 */
final readonly class Conflict
{
    public function __construct(
        public VersionedValue $valueA,
        public VersionedValue $valueB,
        public string         $key,
        public float          $detectedAt,
    )
    {
    }

    /**
     * Creates a conflict from two versioned values.
     */
    public static function fromValues(
        VersionedValue $a,
        VersionedValue $b,
        string         $key,
    ): self
    {
        return new self(
            valueA: $a,
            valueB: $b,
            key: $key,
            detectedAt: microtime(true),
        );
    }
}
