<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\SchemaRouting;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLField;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLObjectType;

/**
 * Handles root field lookup and operation-to-type routing for GraphQL schemas.
 */
final readonly class SchemaRouter
{
    /**
     * @param array<string, GraphQLField> $queryFields
     * @param array<string, GraphQLField> $mutationFields
     */
    public function findRootField(
        string $operationType,
        string $fieldName,
        array  $queryFields,
        array  $mutationFields
    ) : GraphQLField|null
    {
        return match ($operationType) {
            'query'    => $queryFields[$fieldName] ?? null,
            'mutation' => $mutationFields[$fieldName] ?? null,
            default    => null,
        };
    }

    /**
     * @param array<string, GraphQLField> $queryFields
     * @param array<string, GraphQLField> $mutationFields
     */
    public function rootTypeForOperation(
        string $operationType,
        array  $queryFields,
        array  $mutationFields
    ) : GraphQLObjectType
    {
        return match ($operationType) {
            'query'    => new GraphQLObjectType('Query', array_values($queryFields)),
            'mutation' => new GraphQLObjectType('Mutation', array_values($mutationFields)),
            default    => new GraphQLObjectType('Unknown'),
        };
    }
}
