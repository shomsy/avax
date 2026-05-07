<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\GraphQL;

use Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution\GraphQLResolverContext;
use Avax\Components\API\GraphQL\System\Configuration\GraphQLConfiguration;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQL;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLSchema;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class GraphQLTest extends TestCase
{
    public function test_schema_model_describes_query_mutation_and_object_fields() : void
    {
        $schema  = $this->schema();
        $payload = $schema->toArray();

        self::assertSame('Users GraphQL API', $payload['name']);
        self::assertSame('[User!]!', $payload['queries']['users']['type']);
        self::assertSame(['active' => 'Boolean!'], $payload['queries']['users']['arguments']);
        self::assertSame(['users.read'], $payload['queries']['users']['requiredPermissions']);
        self::assertSame('User!', $payload['mutations']['createUser']['type']);
        self::assertSame(['id' => 'ID!', 'name' => 'String!'], $payload['types']['User']['fields']);
    }

    private function schema() : GraphQLSchema
    {
        return GraphQL::schema(name: 'Users GraphQL API')
            ->withQueryField(
                name               : 'users',
                type               : '[User!]!',
                arguments          : ['active' => 'Boolean!'],
                complexityCost     : 4,
                requiredPermissions: ['users.read'],
            )
            ->withMutationField(
                name               : 'createUser',
                type               : 'User!',
                arguments          : ['name' => 'String!'],
                complexityCost     : 3,
                requiredPermissions: ['users.write'],
            )
            ->withObjectType(
                name  : 'User',
                fields: [
                            'id'   => 'ID!',
                            'name' => 'String!',
                        ],
            );
    }

    public function test_operation_validation_reports_auth_depth_complexity_and_shape_errors() : void
    {
        $schema        = $this->schema();
        $configuration = new GraphQLConfiguration(maxDepth: 1, maxComplexity: 4);

        $report = GraphQL::validate(
            schema       : $schema,
            operation    : 'query Users { users(active: true) { id unknown } }',
            configuration: $configuration,
            permissions  : [],
        );

        self::assertFalse($report->isValid());
        self::assertSame(2, $report->depth);
        self::assertSame(5, $report->complexity);
        self::assertSame(['Query.users.unknown'], $report->missingFields);
        self::assertSame(['Query.users'], $report->unauthorizedFields);
        self::assertContains('GraphQL operation depth 2 exceeds max depth 1.', $report->errors);
        self::assertContains('GraphQL operation complexity 5 exceeds max complexity 4.', $report->errors);
        self::assertContains('GraphQL field Query.users.unknown is not defined.', $report->errors);
        self::assertContains('GraphQL field Query.users is not authorized.', $report->errors);
    }

    public function test_query_executor_resolves_data_with_variables_permissions_and_timing() : void
    {
        $executor = GraphQL::executor(schema: $this->schema())
            ->registerResolver(
                typeName : 'Query',
                fieldName: 'users',
                resolver : static function (
                    mixed                  $parent,
                    array                  $arguments,
                    GraphQLResolverContext $context,
                ) : array {
                    self::assertNull($parent);
                    self::assertSame(['users.read'], $context->permissions);
                    self::assertTrue($arguments['active']);

                    return [
                        ['id' => '1', 'name' => 'Ada'],
                        ['id' => '2', 'name' => 'Grace'],
                    ];
                },
            );

        $result = $executor->query(
            operation  : 'query Users($active: Boolean!) { users(active: $active) { id name } }',
            variables  : ['active' => true],
            permissions: ['users.read'],
        );

        self::assertTrue($result->isSuccessful());
        self::assertSame([
                             'users' => [
                                 ['id' => '1', 'name' => 'Ada'],
                                 ['id' => '2', 'name' => 'Grace'],
                             ],
                         ], $result->data);
        self::assertCount(1, $result->timeline->entries());
        self::assertSame('Query', $result->timeline->entries()[0]->typeName);
        self::assertSame('users', $result->timeline->entries()[0]->fieldName);
        self::assertSame(['Query', 'users'], $result->timeline->entries()[0]->path);
        self::assertGreaterThanOrEqual(0, $result->timeline->totalDurationNanoseconds());
    }

    public function test_mutation_executor_rejects_query_operations_and_resolves_mutations() : void
    {
        $executor = GraphQL::executor(schema: $this->schema())
            ->registerResolver(
                typeName : 'Mutation',
                fieldName: 'createUser',
                resolver : static fn (mixed $parent, array $arguments) : array => [
                    'id'   => '3',
                    'name' => $arguments['name'],
                ],
            );

        $wrongOperation = $executor->mutation(
            operation  : 'query Users { users(active: true) { id } }',
            permissions: ['users.read'],
        );
        $created        = $executor->mutation(
            operation  : 'mutation CreateUser { createUser(name: "Lin") { id name } }',
            permissions: ['users.write'],
        );

        self::assertFalse($wrongOperation->isSuccessful());
        self::assertSame(['GraphQL executor expected mutation operation, got query.'], $wrongOperation->errors);
        self::assertTrue($created->isSuccessful());
        self::assertSame(['createUser' => ['id' => '3', 'name' => 'Lin']], $created->data);
    }

    public function test_data_loader_batches_unique_keys_with_configured_chunk_size() : void
    {
        $batches    = [];
        $dataLoader = GraphQL::dataLoader(
            loadFields  : static function (array $keys) use (&$batches) : array {
                $batches[] = $keys;

                return array_combine(
                    $keys,
                    array_map(static fn (int|string $key) : string => sprintf('user-%s', (string) $key), $keys),
                );
            },
            maxBatchSize: 2,
        );

        $dataLoader->queue(1);
        $dataLoader->queue(2);
        $dataLoader->queue(2);
        $dataLoader->queue(3);

        $loaded = $dataLoader->dispatch();

        self::assertSame([[1, 2], [3]], $batches);
        self::assertSame([1 => 'user-1', 2 => 'user-2', 3 => 'user-3'], $loaded);
        self::assertSame([], $dataLoader->queuedKeys());
    }

    public function test_configuration_rejects_unbounded_guards() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('GraphQL max complexity must be at least 1.');

        new GraphQLConfiguration(maxComplexity: 0);
    }
}
