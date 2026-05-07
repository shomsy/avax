<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\PublicSurface;

use Avax\Components\API\GraphQL\System\Capabilities\BatchFieldLoading\DataLoader;
use Avax\Components\API\GraphQL\System\Configuration\GraphQLConfiguration;
use Avax\Components\API\GraphQL\System\Flows\BuildGraphQLSchema\BuildGraphQLSchema;
use Avax\Components\API\GraphQL\System\Flows\ValidateGraphQLOperation\ValidateGraphQLOperation;
use Closure;

final readonly class GraphQL
{
    public static function schema(string $name = 'AvaX GraphQL API') : GraphQLSchema
    {
        return new BuildGraphQLSchema()->build(name: $name);
    }

    public static function executor(
        GraphQLSchema         $schema,
        ?GraphQLConfiguration $configuration = null,
    ) : GraphQLExecutor
    {
        return new GraphQLExecutor(
            schema       : $schema,
            configuration: $configuration ?? new GraphQLConfiguration(),
        );
    }

    /**
     * @param list<string> $permissions
     */
    public static function validate(
        GraphQLSchema         $schema,
        string                $operation,
        ?GraphQLConfiguration $configuration = null,
        array                 $permissions = [],
    ) : GraphQLOperationReport
    {
        return new ValidateGraphQLOperation()->validate(
            schema       : $schema,
            operation    : $operation,
            configuration: $configuration ?? new GraphQLConfiguration(),
            permissions  : $permissions,
        );
    }

    /**
     * @param Closure(list<int|string>): array<int|string, mixed> $loadFields
     */
    public static function dataLoader(Closure $loadFields, int $maxBatchSize = 100) : DataLoader
    {
        return new DataLoader(
            loadFields  : $loadFields,
            maxBatchSize: $maxBatchSize,
        );
    }
}
