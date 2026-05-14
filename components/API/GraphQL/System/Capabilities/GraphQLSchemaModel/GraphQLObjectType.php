<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel;

use InvalidArgumentException;

final readonly class GraphQLObjectType
{
    /**
     * @var array<string, GraphQLField>
     */
    public array $fields;

    /**
     * @param list<GraphQLField> $fields
     */
    public function __construct(
        public string $name,
        array         $fields = [],
    )
    {
        if ($this->name === '') {
            throw new InvalidArgumentException('GraphQL object type name must not be empty.');
        }

        $indexed = [];

        foreach ($fields as $field) {
            $indexed[$field->name] = $field;
        }

        $this->fields = $indexed;
    }

    public function findField(string $name) : GraphQLField|null
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @return list<string>
     */
    public function fieldNames() : array
    {
        return array_keys($this->fields);
    }
}
