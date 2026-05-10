<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Flows\GenerateOpenApiFromRoutes\GenerateOpenApiFromRoutes;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GenerateOpenApiFromRoutes::class)]
final class OpenApiGenerationTest extends TestCase
{
    public function testGeneratesValidOpenApiSpec() : void
    {
        $collection = new RouteCollection();
        $collection->add(new RouteDefinition(new RouteMethod('GET'), '/users', fn() => null));
        $collection->add(new RouteDefinition(new RouteMethod('POST'), '/users', fn() => null));
        $collection->add(new RouteDefinition(new RouteMethod('GET'), '/users/{id}', fn() => null));

        $generator = new GenerateOpenApiFromRoutes(title: 'Test API', version: '2.0.0');
        $spec = $generator->fromCollection($collection);

        self::assertSame('3.0.3', $spec['openapi']);
        self::assertSame('Test API', $spec['info']['title']);
        self::assertSame('2.0.0', $spec['info']['version']);
        self::assertArrayHasKey('paths', $spec);
        self::assertCount(2, $spec['paths']); // /users (GET+POST) and /users/{id} (GET)
        self::assertArrayHasKey('get', $spec['paths']['/users']);
        self::assertArrayHasKey('post', $spec['paths']['/users']);
        self::assertArrayHasKey('get', $spec['paths']['/users/{id}']);
    }

    public function testExtractsPathParameters() : void
    {
        $collection = new RouteCollection();
        $collection->add(new RouteDefinition(new RouteMethod('GET'), '/users/{id}/posts/{postId}', fn() => null));

        $generator = new GenerateOpenApiFromRoutes();
        $spec = $generator->fromCollection($collection);

        $params = $spec['paths']['/users/{id}/posts/{postId}']['get']['parameters'];
        self::assertCount(2, $params);
        self::assertSame('id', $params[0]['name']);
        self::assertSame('path', $params[0]['in']);
        self::assertTrue($params[0]['required']);
        self::assertSame('postId', $params[1]['name']);
    }

    public function testGeneratesOperationId() : void
    {
        $collection = new RouteCollection();
        $collection->add(new RouteDefinition(new RouteMethod('GET'), '/users/{id}', fn() => null));

        $generator = new GenerateOpenApiFromRoutes();
        $spec = $generator->fromCollection($collection);

        self::assertSame('get_users', $spec['paths']['/users/{id}']['get']['operationId']);
    }

    public function testGeneratesSummary() : void
    {
        $collection = new RouteCollection();
        $collection->add(new RouteDefinition(new RouteMethod('POST'), '/users', fn() => null));

        $generator = new GenerateOpenApiFromRoutes();
        $spec = $generator->fromCollection($collection);

        self::assertSame('Create users', $spec['paths']['/users']['post']['summary']);
    }

    public function testEmptyCollectionProducesValidSpec() : void
    {
        $collection = new RouteCollection();

        $generator = new GenerateOpenApiFromRoutes();
        $spec = $generator->fromCollection($collection);

        self::assertSame('3.0.3', $spec['openapi']);
        self::assertSame([], $spec['paths']);
    }

    public function testAllHttpMethodsAreRepresented() : void
    {
        $collection = new RouteCollection();
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $collection->add(new RouteDefinition(new RouteMethod($method), '/resource', fn() => null));
        }

        $generator = new GenerateOpenApiFromRoutes();
        $spec = $generator->fromCollection($collection);

        self::assertArrayHasKey('get', $spec['paths']['/resource']);
        self::assertArrayHasKey('post', $spec['paths']['/resource']);
        self::assertArrayHasKey('put', $spec['paths']['/resource']);
        self::assertArrayHasKey('patch', $spec['paths']['/resource']);
        self::assertArrayHasKey('delete', $spec['paths']['/resource']);
    }

    public function testResponsesAreIncluded() : void
    {
        $collection = new RouteCollection();
        $collection->add(new RouteDefinition(new RouteMethod('GET'), '/health', fn() => null));

        $generator = new GenerateOpenApiFromRoutes();
        $spec = $generator->fromCollection($collection);

        self::assertArrayHasKey('responses', $spec['paths']['/health']['get']);
        self::assertArrayHasKey('200', $spec['paths']['/health']['get']['responses']);
        self::assertSame('Successful response', $spec['paths']['/health']['get']['responses']['200']['description']);
    }
}
