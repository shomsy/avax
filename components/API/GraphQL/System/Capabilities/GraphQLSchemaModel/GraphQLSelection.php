<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel;

final readonly class GraphQLSelection
{
    /**
     * @param array<string, mixed>   $arguments
     * @param list<GraphQLSelection> $selections
     */
    public function __construct(
        public string  $name,
        public string|null $alias = null,
        public array   $arguments = [],
        public array   $selections = [],
    ) {}

    public function dataKey() : string
    {
        return $this->alias ?? $this->name;
    }

    public function hasSelections() : bool
    {
        return $this->selections !== [];
    }
}
