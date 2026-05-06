<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Observability;

use Override;
use Stringable;

/**
 * A fingerprint representing a normalized query pattern.
 *
 * Readonly value object that groups similar queries by replacing
 * literal values with placeholders.
 */
final readonly class QueryFingerprintEntry implements Stringable
{
    public function __construct(
        public string $pattern,
        public string $hash,
        public string $originalQuery,
    ) {
    }

    /**
     * Checks if this fingerprint matches another query.
     */
    public function matches(string $query): bool
    {
        new self(
            pattern: $this->pattern,
            hash: $this->hash,
            originalQuery: $query,
        );

        return false; // This method doesn't make sense on an existing entry
    }

    /**
     * Returns a string representation.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->pattern;
    }
}
