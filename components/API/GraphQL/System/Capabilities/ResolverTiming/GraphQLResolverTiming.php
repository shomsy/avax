<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\ResolverTiming;

final readonly class GraphQLResolverTiming
{
    /**
     * @param list<string> $path
     */
    public function __construct(
        public string $typeName,
        public string $fieldName,
        public array  $path,
        public int    $durationNanoseconds,
    ) {}

    public function durationMilliseconds() : float
    {
        return $this->durationNanoseconds / 1_000_000;
    }
}
