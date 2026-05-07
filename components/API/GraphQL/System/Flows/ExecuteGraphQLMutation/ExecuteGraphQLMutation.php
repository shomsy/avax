<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Flows\ExecuteGraphQLMutation;

use Avax\Components\API\GraphQL\System\Capabilities\BatchFieldLoading\DataLoader;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution\GraphQLResolverRegistry;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution\ResolveGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverTiming\GraphQLResolverTimeline;
use Avax\Components\API\GraphQL\System\Configuration\GraphQLConfiguration;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLExecutionResult;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLSchema;

final readonly class ExecuteGraphQLMutation
{
    public function __construct(
        private ResolveGraphQLOperation $resolveGraphQLOperation = new ResolveGraphQLOperation(),
    ) {}

    /**
     * @param array<string, mixed> $variables
     * @param list<string>         $permissions
     */
    public function execute(
        GraphQLSchema           $schema,
        string                  $operation,
        GraphQLConfiguration    $configuration,
        GraphQLResolverRegistry $resolvers,
        DataLoader              $dataLoader,
        GraphQLResolverTimeline $timeline,
        array                   $variables = [],
        array                   $permissions = [],
    ) : GraphQLExecutionResult
    {
        return $this->resolveGraphQLOperation->resolve(
            schema               : $schema,
            operation            : $operation,
            expectedOperationType: 'mutation',
            configuration        : $configuration,
            resolvers            : $resolvers,
            dataLoader           : $dataLoader,
            timeline             : $timeline,
            variables            : $variables,
            permissions          : $permissions,
        );
    }
}
