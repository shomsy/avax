<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution;

use Closure;

final class GraphQLResolverRegistry
{
    /**
     * @var array<string, Closure(mixed, array<string, mixed>, GraphQLResolverContext): mixed>
     */
    private array $resolvers = [];

    /**
     * @param Closure(mixed, array<string, mixed>, GraphQLResolverContext): mixed $resolver
     */
    public function register(string $typeName, string $fieldName, Closure $resolver) : void
    {
        $this->resolvers[$this->key(typeName: $typeName, fieldName: $fieldName)] = $resolver;
    }

    private function key(string $typeName, string $fieldName) : string
    {
        return sprintf('%s.%s', $typeName, $fieldName);
    }

    /**
     * @return (Closure(mixed, array<string, mixed>, GraphQLResolverContext): mixed)|null
     */
    public function resolverFor(string $typeName, string $fieldName) : Closure|null
    {
        return $this->resolvers[$this->key(typeName: $typeName, fieldName: $fieldName)] ?? null;
    }
}
