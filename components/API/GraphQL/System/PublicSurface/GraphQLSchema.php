<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\PublicSurface;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLField;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLObjectType;

final class GraphQLSchema
{
    /**
     * @var array<string, GraphQLField>
     */
    private array $queryFields;

    /**
     * @var array<string, GraphQLField>
     */
    private array $mutationFields;

    /**
     * @var array<string, GraphQLObjectType>
     */
    private array $types;

    /**
     * @param array<string, GraphQLField>      $queryFields
     * @param array<string, GraphQLField>      $mutationFields
     * @param array<string, GraphQLObjectType> $types
     */
    public function __construct(
        public readonly string $name,
        array                  $queryFields = [],
        array                  $mutationFields = [],
        array                  $types = [],
    )
    {
        $this->queryFields    = $queryFields;
        $this->mutationFields = $mutationFields;
        $this->types          = $types;
    }

    public static function define(string $name = 'AvaX GraphQL API') : self
    {
        return new self(name: $name);
    }

    /**
     * @param array<string, string> $arguments
     * @param list<string>          $requiredPermissions
     */
    public function withQueryField(
        string $name,
        string $type,
        array  $arguments = [],
        int    $complexityCost = 1,
        array  $requiredPermissions = [],
    ) : self
    {
        $schema                     = clone $this;
        $schema->queryFields[$name] = new GraphQLField(
            name               : $name,
            type               : $type,
            arguments          : $arguments,
            complexityCost     : $complexityCost,
            requiredPermissions: $requiredPermissions,
        );

        return $schema;
    }

    /**
     * @param array<string, string> $arguments
     * @param list<string>          $requiredPermissions
     */
    public function withMutationField(
        string $name,
        string $type,
        array  $arguments = [],
        int    $complexityCost = 1,
        array  $requiredPermissions = [],
    ) : self
    {
        $schema                        = clone $this;
        $schema->mutationFields[$name] = new GraphQLField(
            name               : $name,
            type               : $type,
            arguments          : $arguments,
            complexityCost     : $complexityCost,
            requiredPermissions: $requiredPermissions,
        );

        return $schema;
    }

    /**
     * @param array<string, string> $fields
     */
    public function withObjectType(string $name, array $fields) : self
    {
        $schema               = clone $this;
        $schema->types[$name] = new GraphQLObjectType(
            name  : $name,
            fields: $this->fieldsFromMap($fields),
        );

        return $schema;
    }

    /**
     * @param array<string, string> $fields
     *
     * @return list<GraphQLField>
     */
    private function fieldsFromMap(array $fields) : array
    {
        $definitions = [];

        foreach ($fields as $name => $type) {
            $definitions[] = new GraphQLField(name: $name, type: $type);
        }

        return $definitions;
    }

    public function findType(string $name) : ?GraphQLObjectType
    {
        return $this->types[$name] ?? null;
    }

    public function findRootField(string $operationType, string $fieldName) : ?GraphQLField
    {
        return match ($operationType) {
            'query'    => $this->queryFields[$fieldName] ?? null,
            'mutation' => $this->mutationFields[$fieldName] ?? null,
            default    => null,
        };
    }

    public function rootTypeForOperation(string $operationType) : GraphQLObjectType
    {
        return match ($operationType) {
            'query'    => new GraphQLObjectType('Query', array_values($this->queryFields)),
            'mutation' => new GraphQLObjectType('Mutation', array_values($this->mutationFields)),
            default    => new GraphQLObjectType('Unknown'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'name'      => $this->name,
            'queries'   => array_map(
                static fn (GraphQLField $field) : array => [
                    'type'                => $field->type,
                    'arguments'           => $field->arguments,
                    'complexityCost'      => $field->complexityCost,
                    'requiredPermissions' => $field->requiredPermissions,
                ],
                $this->queryFields,
            ),
            'mutations' => array_map(
                static fn (GraphQLField $field) : array => [
                    'type'                => $field->type,
                    'arguments'           => $field->arguments,
                    'complexityCost'      => $field->complexityCost,
                    'requiredPermissions' => $field->requiredPermissions,
                ],
                $this->mutationFields,
            ),
            'types'     => array_map(
                static fn (GraphQLObjectType $type) : array => [
                    'fields' => array_map(
                        static fn (GraphQLField $field) : string => $field->type,
                        $type->fields,
                    ),
                ],
                $this->types,
            ),
        ];
    }
}
