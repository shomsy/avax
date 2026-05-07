<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Flows\BuildGraphQLSchema;

use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLSchema;

final readonly class BuildGraphQLSchema
{
    public function build(string $name = 'AvaX GraphQL API') : GraphQLSchema
    {
        return GraphQLSchema::define(name: $name);
    }
}
