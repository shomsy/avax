<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\PublicSurface;

use Avax\Components\API\GraphQL\System\Capabilities\ResolverTiming\GraphQLResolverTimeline;

final readonly class GraphQLExecutionResult
{
    /**
     * @param array<string, mixed> $data
     * @param list<string>         $errors
     */
    public function __construct(
        public array                   $data,
        public array                   $errors,
        public GraphQLResolverTimeline $timeline,
    ) {}

    public function isSuccessful() : bool
    {
        return $this->errors === [];
    }
}
