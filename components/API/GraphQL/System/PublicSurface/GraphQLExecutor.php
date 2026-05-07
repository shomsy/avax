<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\PublicSurface;

use Avax\Components\API\GraphQL\System\Capabilities\BatchFieldLoading\DataLoader;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution\GraphQLResolverContext;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution\GraphQLResolverRegistry;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverTiming\GraphQLResolverTimeline;
use Avax\Components\API\GraphQL\System\Configuration\GraphQLConfiguration;
use Avax\Components\API\GraphQL\System\Flows\ExecuteGraphQLMutation\ExecuteGraphQLMutation;
use Avax\Components\API\GraphQL\System\Flows\ExecuteGraphQLQuery\ExecuteGraphQLQuery;
use Avax\Components\API\GraphQL\System\Flows\ValidateGraphQLOperation\ValidateGraphQLOperation;
use Closure;

final class GraphQLExecutor
{
    private GraphQLResolverRegistry $resolvers;

    private DataLoader $dataLoader;

    private GraphQLResolverTimeline $timeline;

    public function __construct(
        private readonly GraphQLSchema        $schema,
        private readonly GraphQLConfiguration $configuration = new GraphQLConfiguration(),
    )
    {
        $this->resolvers  = new GraphQLResolverRegistry();
        $this->dataLoader = new DataLoader(static fn (array $keys) : array => array_fill_keys($keys, null));
        $this->timeline   = new GraphQLResolverTimeline();
    }

    /**
     * @param Closure(mixed, array<string, mixed>, GraphQLResolverContext): mixed $resolver
     */
    public function registerResolver(string $typeName, string $fieldName, Closure $resolver) : self
    {
        $this->resolvers->register(
            typeName : $typeName,
            fieldName: $fieldName,
            resolver : $resolver,
        );

        return $this;
    }

    /**
     * @param Closure(list<int|string>): array<int|string, mixed> $loadFields
     */
    public function useDataLoader(Closure $loadFields) : self
    {
        $this->dataLoader = new DataLoader(
            loadFields  : $loadFields,
            maxBatchSize: $this->configuration->maxBatchSize,
        );

        return $this;
    }

    /**
     * @param array<string, mixed> $variables
     * @param list<string>         $permissions
     */
    public function query(string $operation, array $variables = [], array $permissions = []) : GraphQLExecutionResult
    {
        return new ExecuteGraphQLQuery()->execute(
            schema       : $this->schema,
            operation    : $operation,
            configuration: $this->configuration,
            resolvers    : $this->resolvers,
            dataLoader   : $this->dataLoader,
            timeline     : $this->timeline,
            variables    : $variables,
            permissions  : $permissions,
        );
    }

    /**
     * @param array<string, mixed> $variables
     * @param list<string>         $permissions
     */
    public function mutation(string $operation, array $variables = [], array $permissions = []) : GraphQLExecutionResult
    {
        return new ExecuteGraphQLMutation()->execute(
            schema       : $this->schema,
            operation    : $operation,
            configuration: $this->configuration,
            resolvers    : $this->resolvers,
            dataLoader   : $this->dataLoader,
            timeline     : $this->timeline,
            variables    : $variables,
            permissions  : $permissions,
        );
    }

    /**
     * @param list<string> $permissions
     */
    public function validate(string $operation, array $permissions = []) : GraphQLOperationReport
    {
        return new ValidateGraphQLOperation()->validate(
            schema       : $this->schema,
            operation    : $operation,
            configuration: $this->configuration,
            permissions  : $permissions,
        );
    }

    public function timeline() : GraphQLResolverTimeline
    {
        return $this->timeline;
    }
}
