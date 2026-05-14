<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\SchemaSerialization;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLField;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLObjectType;

final readonly class SchemaToArray
{
    /**
     * @param array<string, GraphQLField>      $queryFields
     * @param array<string, GraphQLField>      $mutationFields
     * @param array<string, GraphQLObjectType> $types
     *
     * @return array<string, mixed>
     */
    public function convert(
        string $name,
        array  $queryFields,
        array  $mutationFields,
        array  $types,
    ) : array
    {
        return [
            'name'      => $name,
            'queries'   => array_map(
                static fn (GraphQLField $field) : array => [
                    'type'                => $field->type,
                    'arguments'           => $field->arguments,
                    'complexityCost'      => $field->complexityCost,
                    'requiredPermissions' => $field->requiredPermissions,
                ],
                $queryFields,
            ),
            'mutations' => array_map(
                static fn (GraphQLField $field) : array => [
                    'type'                => $field->type,
                    'arguments'           => $field->arguments,
                    'complexityCost'      => $field->complexityCost,
                    'requiredPermissions' => $field->requiredPermissions,
                ],
                $mutationFields,
            ),
            'types'     => array_map(
                static fn (GraphQLObjectType $type) : array => [
                    'fields' => array_map(
                        static fn (GraphQLField $field) : string => $field->type,
                        $type->fields,
                    ),
                ],
                $types,
            ),
        ];
    }
}
