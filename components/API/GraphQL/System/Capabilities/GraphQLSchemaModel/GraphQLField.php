<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel;

use InvalidArgumentException;

final readonly class GraphQLField
{
    /**
     * @param array<string, string> $arguments
     * @param list<string>          $requiredPermissions
     */
    public function __construct(
        public string $name,
        public string $type,
        public array  $arguments = [],
        public int    $complexityCost = 1,
        public array  $requiredPermissions = [],
    )
    {
        if ($this->name === '') {
            throw new InvalidArgumentException('GraphQL field name must not be empty.');
        }

        if ($this->type === '') {
            throw new InvalidArgumentException('GraphQL field type must not be empty.');
        }

        if ($this->complexityCost < 1) {
            throw new InvalidArgumentException('GraphQL field complexity cost must be at least 1.');
        }
    }

    public function namedType() : string
    {
        $type = trim($this->type);
        $type = rtrim($type, '!');

        if (str_starts_with($type, '[') && str_ends_with($type, ']')) {
            $type = substr($type, 1, -1);
        }

        return trim(rtrim($type, '!'));
    }

    public function isList() : bool
    {
        return str_starts_with(rtrim($this->type, '!'), '[');
    }

    public function requiresPermissions() : bool
    {
        return $this->requiredPermissions !== [];
    }

    /**
     * @return list<string>
     */
    public function requiredArguments() : array
    {
        $required = [];

        foreach ($this->arguments as $name => $type) {
            if (str_ends_with($type, '!')) {
                $required[] = $name;
            }
        }

        return $required;
    }
}
