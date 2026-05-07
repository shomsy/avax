<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\GraphQLAuthorization;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLField;

final readonly class AuthorizeGraphQLField
{
    /**
     * @param list<string> $permissions
     */
    public function authorize(GraphQLField $field, array $permissions) : bool
    {
        if (! $field->requiresPermissions()) {
            return true;
        }

        foreach ($field->requiredPermissions as $requiredPermission) {
            if (! in_array($requiredPermission, $permissions, true)) {
                return false;
            }
        }

        return true;
    }
}
