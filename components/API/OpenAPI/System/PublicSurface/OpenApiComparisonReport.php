<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\PublicSurface;

final readonly class OpenApiComparisonReport
{
    /**
     * @param list<string> $removedOperations
     * @param list<string> $changedOperations
     */
    public function __construct(
        public array $removedOperations = [],
        public array $changedOperations = [],
    ) {}

    public function hasCompatibilityIssues() : bool
    {
        return $this->removedOperations !== [] || $this->changedOperations !== [];
    }
}
