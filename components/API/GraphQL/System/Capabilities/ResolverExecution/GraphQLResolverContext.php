<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution;

use Avax\Components\API\GraphQL\System\Capabilities\BatchFieldLoading\DataLoader;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLSelection;

final readonly class GraphQLResolverContext
{
    /**
     * @param array<string, mixed> $variables
     * @param list<string>         $permissions
     * @param list<string>         $path
     */
    public function __construct(
        public array      $variables,
        public array      $permissions,
        public DataLoader $dataLoader,
        public array      $path,
    ) {}

    /**
     * @param list<string> $path
     */
    public function withPath(array $path) : self
    {
        return new self(
            variables  : $this->variables,
            permissions: $this->permissions,
            dataLoader : $this->dataLoader,
            path       : $path,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function argumentsFor(GraphQLSelection $selection) : array
    {
        $arguments = [];

        foreach ($selection->arguments as $name => $value) {
            $arguments[$name] = $this->resolveArgument(value: $value);
        }

        return $arguments;
    }

    private function resolveArgument(mixed $value) : mixed
    {
        if (is_array($value) && array_key_exists('variable', $value) && is_string($value['variable'])) {
            return $this->variables[$value['variable']] ?? null;
        }

        return $value;
    }
}
