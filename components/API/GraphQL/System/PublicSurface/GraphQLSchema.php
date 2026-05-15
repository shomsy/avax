<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\PublicSurface;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLField;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLObjectType;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaFieldAssembly\AssembleFieldsFromMap;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaRouting\SchemaRouter;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaSerialization\SchemaToArray;

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

    private AssembleFieldsFromMap $fieldAssembler;
    private SchemaToArray         $schemaSerializer;
    private SchemaRouter $schemaRouter;

    /**
     * @param array<string, GraphQLField>      $queryFields
     * @param array<string, GraphQLField>      $mutationFields
     * @param array<string, GraphQLObjectType> $types
     */
    public function __construct(
        public readonly string     $name,
        AssembleFieldsFromMap $fieldAssembler,
        SchemaToArray         $schemaSerializer,
        SchemaRouter          $schemaRouter,
        array                      $queryFields = [],
        array                      $mutationFields = [],
        array                      $types = [],
    )
    {
        $this->queryFields      = $queryFields;
        $this->mutationFields   = $mutationFields;
        $this->types            = $types;
        $this->fieldAssembler = $fieldAssembler;
        $this->schemaSerializer = $schemaSerializer;
        $this->schemaRouter = $schemaRouter;
    }

    public static function define(string $name = 'AvaX GraphQL API') : self
    {
        return new self(
            name            : $name,
            fieldAssembler  : new AssembleFieldsFromMap(),
            schemaSerializer: new SchemaToArray(),
            schemaRouter    : new SchemaRouter(),
        );
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
            fields: $this->fieldAssembler->assemble($fields),
        );

        return $schema;
    }

    public function findType(string $name) : GraphQLObjectType|null
    {
        return $this->types[$name] ?? null;
    }

    public function findRootField(string $operationType, string $fieldName) : GraphQLField|null
    {
        return $this->schemaRouter->findRootField(
            operationType : $operationType,
            fieldName     : $fieldName,
            queryFields   : $this->queryFields,
            mutationFields: $this->mutationFields,
        );
    }

    public function rootTypeForOperation(string $operationType) : GraphQLObjectType
    {
        return $this->schemaRouter->rootTypeForOperation(
            operationType : $operationType,
            queryFields   : $this->queryFields,
            mutationFields: $this->mutationFields,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return $this->schemaSerializer->convert(
            name          : $this->name,
            queryFields   : $this->queryFields,
            mutationFields: $this->mutationFields,
            types         : $this->types,
        );
    }
}
