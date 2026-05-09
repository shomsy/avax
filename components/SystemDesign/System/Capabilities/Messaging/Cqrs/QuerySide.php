<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Messaging\Cqrs;

/**
 * CQRS query side model.
 *
 * @experimental V3 labs
 *
 * Models the read side of a CQRS architecture:
 * query handlers, read models, and read consistency.
 */
final readonly class QuerySide
{
    public function __construct(
        public string $aggregate,
        public string $readConsistencyModel,
        public int    $queryTimeoutMs,
        public bool   $usesCaching,
        public int    $cacheTtlSeconds,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->aggregate === '') {
            $errors[] = 'aggregate must not be empty.';
        }

        if ($this->queryTimeoutMs <= 0) {
            $errors[] = 'query_timeout_ms must be positive.';
        }

        if ($this->usesCaching && $this->cacheTtlSeconds < 1) {
            $errors[] = 'cache_ttl_seconds must be >= 1 when caching is enabled.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
