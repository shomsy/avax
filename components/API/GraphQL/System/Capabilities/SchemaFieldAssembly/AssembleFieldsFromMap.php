<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\SchemaFieldAssembly;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLField;

final readonly class AssembleFieldsFromMap
{
    /**
     * @param array<string, string> $fields
     *
     * @return list<GraphQLField>
     */
    public function assemble(array $fields) : array
    {
        $definitions = [];

        foreach ($fields as $name => $type) {
            $definitions[] = new GraphQLField(name: $name, type: $type);
        }

        return $definitions;
    }
}
