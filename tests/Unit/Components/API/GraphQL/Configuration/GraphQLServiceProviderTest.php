<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\GraphQL\Configuration;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLAuthorization\AuthorizeGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\ParseGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\OperationComplexity\QueryComplexityAnalyzer;
use Avax\Components\API\GraphQL\System\Capabilities\OperationComplexity\QueryDepthLimiter;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution\ResolveGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaFieldAssembly\AssembleFieldsFromMap;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaRouting\SchemaRouter;
use Avax\Components\API\GraphQL\System\Capabilities\SchemaSerialization\SchemaToArray;
use Avax\Components\API\GraphQL\System\Configuration\GraphQLServiceProvider;
use Avax\Components\API\GraphQL\System\Flows\ExecuteGraphQLMutation\ExecuteGraphQLMutation;
use Avax\Components\API\GraphQL\System\Flows\ExecuteGraphQLQuery\ExecuteGraphQLQuery;
use Avax\Components\API\GraphQL\System\Flows\ValidateGraphQLOperation\ValidateGraphQLOperation;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class GraphQLServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private GraphQLServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new GraphQLServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_parse_graphql_operation_resolves(): void
    {
        $capability = $this->container->get(ParseGraphQLOperation::class);

        $this->assertInstanceOf(ParseGraphQLOperation::class, $capability);
    }

    public function test_query_depth_limiter_resolves(): void
    {
        $capability = $this->container->get(QueryDepthLimiter::class);

        $this->assertInstanceOf(QueryDepthLimiter::class, $capability);
    }

    public function test_query_complexity_analyzer_resolves(): void
    {
        $capability = $this->container->get(QueryComplexityAnalyzer::class);

        $this->assertInstanceOf(QueryComplexityAnalyzer::class, $capability);
    }

    public function test_authorize_graphql_operation_resolves(): void
    {
        $capability = $this->container->get(AuthorizeGraphQLOperation::class);

        $this->assertInstanceOf(AuthorizeGraphQLOperation::class, $capability);
    }

    public function test_validate_graphql_operation_resolves(): void
    {
        $flow = $this->container->get(ValidateGraphQLOperation::class);

        $this->assertInstanceOf(ValidateGraphQLOperation::class, $flow);
    }

    public function test_resolve_graphql_operation_resolves(): void
    {
        $capability = $this->container->get(ResolveGraphQLOperation::class);

        $this->assertInstanceOf(ResolveGraphQLOperation::class, $capability);
    }

    public function test_execute_graphql_query_resolves(): void
    {
        $flow = $this->container->get(ExecuteGraphQLQuery::class);

        $this->assertInstanceOf(ExecuteGraphQLQuery::class, $flow);
    }

    public function test_execute_graphql_mutation_resolves(): void
    {
        $flow = $this->container->get(ExecuteGraphQLMutation::class);

        $this->assertInstanceOf(ExecuteGraphQLMutation::class, $flow);
    }

    public function test_assemble_fields_from_map_resolves(): void
    {
        $capability = $this->container->get(AssembleFieldsFromMap::class);

        $this->assertInstanceOf(AssembleFieldsFromMap::class, $capability);
    }

    public function test_schema_router_resolves(): void
    {
        $capability = $this->container->get(SchemaRouter::class);

        $this->assertInstanceOf(SchemaRouter::class, $capability);
    }

    public function test_schema_to_array_resolves(): void
    {
        $capability = $this->container->get(SchemaToArray::class);

        $this->assertInstanceOf(SchemaToArray::class, $capability);
    }
}
