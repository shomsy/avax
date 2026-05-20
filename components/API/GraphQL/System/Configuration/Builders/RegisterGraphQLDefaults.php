<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Configuration\Builders;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLAuthorization\AuthorizeGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\ParseGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\OperationComplexity\QueryComplexityAnalyzer;
use Avax\Components\API\GraphQL\System\Capabilities\OperationComplexity\QueryDepthLimiter;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution\ResolveGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaFieldAssembly\AssembleFieldsFromMap;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaRouting\SchemaRouter;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaSerialization\SchemaToArray;
use Avax\Components\API\GraphQL\System\Flows\ExecuteGraphQLMutation\ExecuteGraphQLMutation;
use Avax\Components\API\GraphQL\System\Flows\ExecuteGraphQLQuery\ExecuteGraphQLQuery;
use Avax\Components\API\GraphQL\System\Flows\ValidateGraphQLOperation\ValidateGraphQLOperation;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

final readonly class RegisterGraphQLDefaults
{
    public function register(ContainerInterface $container) : void
    {
        // === Parsing ===

        $container->singleton(
            ParseGraphQLOperation::class,
            static fn () : ParseGraphQLOperation => new ParseGraphQLOperation(),
        );

        // === Complexity ===

        $container->singleton(
            QueryDepthLimiter::class,
            static fn () : QueryDepthLimiter => new QueryDepthLimiter(),
        );

        $container->singleton(
            QueryComplexityAnalyzer::class,
            static fn () : QueryComplexityAnalyzer => new QueryComplexityAnalyzer(),
        );

        // === Authorization ===

        $container->singleton(
            AuthorizeGraphQLOperation::class,
            static fn () : AuthorizeGraphQLOperation => new AuthorizeGraphQLOperation(),
        );

        // === Validation ===

        $container->singleton(
            ValidateGraphQLOperation::class,
            static fn (ContainerInterface $c) : ValidateGraphQLOperation => new ValidateGraphQLOperation(
                parseGraphQLOperation    : $c->get(ParseGraphQLOperation::class),
                queryDepthLimiter        : $c->get(QueryDepthLimiter::class),
                queryComplexityAnalyzer  : $c->get(QueryComplexityAnalyzer::class),
                authorizeGraphQLOperation: $c->get(AuthorizeGraphQLOperation::class),
            ),
        );

        // === Resolver Execution ===

        $container->singleton(
            ResolveGraphQLOperation::class,
            static fn () : ResolveGraphQLOperation => new ResolveGraphQLOperation(),
        );

        // === Query/Mutation Execution ===

        $container->singleton(
            ExecuteGraphQLQuery::class,
            static fn (ContainerInterface $c) : ExecuteGraphQLQuery => new ExecuteGraphQLQuery(
                resolveGraphQLOperation: $c->get(ResolveGraphQLOperation::class),
            ),
        );

        $container->singleton(
            ExecuteGraphQLMutation::class,
            static fn (ContainerInterface $c) : ExecuteGraphQLMutation => new ExecuteGraphQLMutation(
                resolveGraphQLOperation: $c->get(ResolveGraphQLOperation::class),
            ),
        );

        // === Schema Utilities ===

        $container->singleton(
            AssembleFieldsFromMap::class,
            static fn () : AssembleFieldsFromMap => new AssembleFieldsFromMap(),
        );

        $container->singleton(
            SchemaRouter::class,
            static fn () : SchemaRouter => new SchemaRouter(),
        );

        $container->singleton(
            SchemaToArray::class,
            static fn () : SchemaToArray => new SchemaToArray(),
        );
    }
}
