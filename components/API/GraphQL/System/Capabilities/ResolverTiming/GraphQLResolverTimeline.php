<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\ResolverTiming;

final class GraphQLResolverTimeline
{
    /**
     * @var list<GraphQLResolverTiming>
     */
    private array $entries = [];

    public function record(GraphQLResolverTiming $timing) : void
    {
        $this->entries[] = $timing;
    }

    /**
     * @return list<GraphQLResolverTiming>
     */
    public function entries() : array
    {
        return $this->entries;
    }

    public function totalDurationNanoseconds() : int
    {
        return array_sum(
            array_map(
                static fn (GraphQLResolverTiming $timing) : int => $timing->durationNanoseconds,
                $this->entries,
            ),
        );
    }
}
