<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\GraphQLAuthorization;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLObjectType;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLSelection;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\ParsedGraphQLOperation;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLSchema;

final readonly class AuthorizeGraphQLOperation
{
    public function __construct(
        private AuthorizeGraphQLField $authorizeGraphQLField = new AuthorizeGraphQLField(),
    ) {}

    /**
     * @param list<string> $permissions
     *
     * @return list<string>
     */
    public function unauthorizedFields(
        GraphQLSchema          $schema,
        ParsedGraphQLOperation $operation,
        array                  $permissions,
    ) : array
    {
        return $this->scanSelections(
            schema     : $schema,
            type       : $schema->rootTypeForOperation($operation->type),
            selections : $operation->selections,
            permissions: $permissions,
            path       : ucfirst($operation->type),
        );
    }

    /**
     * @param list<GraphQLSelection> $selections
     * @param list<string>           $permissions
     *
     * @return list<string>
     */
    private function scanSelections(
        GraphQLSchema     $schema,
        GraphQLObjectType $type,
        array             $selections,
        array             $permissions,
        string            $path,
    ) : array
    {
        $unauthorized = [];

        foreach ($selections as $selection) {
            $field = $type->findField($selection->name);

            if ($field === null) {
                continue;
            }

            $fieldPath = sprintf('%s.%s', $path, $selection->name);

            if (! $this->authorizeGraphQLField->authorize(field: $field, permissions: $permissions)) {
                $unauthorized[] = $fieldPath;
            }

            $childType = $schema->findType($field->namedType());

            if ($childType !== null && $selection->selections !== []) {
                array_push(
                       $unauthorized,
                    ...$this->scanSelections(
                    schema     : $schema,
                    type       : $childType,
                    selections : $selection->selections,
                    permissions: $permissions,
                    path       : $fieldPath,
                ),
                );
            }
        }

        return $unauthorized;
    }
}
