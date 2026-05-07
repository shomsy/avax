<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\OperationComplexity;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLObjectType;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLSelection;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\ParsedGraphQLOperation;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLSchema;

final readonly class QueryComplexityAnalyzer
{
    public function complexity(GraphQLSchema $schema, ParsedGraphQLOperation $operation) : int
    {
        return $this->selectionComplexity(
            schema    : $schema,
            type      : $schema->rootTypeForOperation($operation->type),
            selections: $operation->selections,
        );
    }

    /**
     * @param list<GraphQLSelection> $selections
     */
    private function selectionComplexity(GraphQLSchema $schema, GraphQLObjectType $type, array $selections) : int
    {
        $complexity = 0;

        foreach ($selections as $selection) {
            $field = $type->findField($selection->name);

            if ($field === null) {
                continue;
            }

            $complexity += $field->complexityCost;

            $childType = $schema->findType($field->namedType());

            if ($childType !== null) {
                $complexity += $this->selectionComplexity(
                    schema    : $schema,
                    type      : $childType,
                    selections: $selection->selections,
                );
            }
        }

        return $complexity;
    }
}
