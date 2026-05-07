<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Configuration;

use InvalidArgumentException;

final readonly class GraphQLConfiguration
{
    public function __construct(
        public int  $maxDepth = 8,
        public int  $maxComplexity = 100,
        public int  $maxBatchSize = 100,
        public bool $recordResolverTiming = true,
    )
    {
        if ($this->maxDepth < 1) {
            throw new InvalidArgumentException('GraphQL max depth must be at least 1.');
        }

        if ($this->maxComplexity < 1) {
            throw new InvalidArgumentException('GraphQL max complexity must be at least 1.');
        }

        if ($this->maxBatchSize < 1) {
            throw new InvalidArgumentException('GraphQL max batch size must be at least 1.');
        }
    }
}
