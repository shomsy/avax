<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel;

final readonly class ParsedGraphQLOperation
{
    /**
     * @param list<GraphQLSelection> $selections
     */
    public function __construct(
        public string  $type,
        public string|null $name,
        public array   $selections,
    ) {}

    public function isQuery() : bool
    {
        return $this->type === 'query';
    }

    public function isMutation() : bool
    {
        return $this->type === 'mutation';
    }
}
