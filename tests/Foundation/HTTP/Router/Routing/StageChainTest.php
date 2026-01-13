<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests\Unit;

use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\Exceptions\StageOrderException;
use Avax\HTTP\Router\Routing\RouteStage;
use Avax\HTTP\Router\Routing\StageChain;
use Avax\HTTP\URI\UriBuilder;
use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use RuntimeException;

final class StageChainTest extends TestCase
{
    public function test_valid_pipeline_assembles() : void
    {
        $container = new FakeContainer;
        $logger    = new NullLogger;
        $chain     = new StageChain(container: $container, logger: $logger);

        $response = $this->createMock(ResponseInterface::class);
        $core     = static fn(Request $request) : ResponseInterface => $response;

        $pipeline = $chain->create(
            stages    : [SampleStage::class],
            middleware: [SampleMiddleware::class],
            core      : $core
        );

        $result = $pipeline(new Request(serverParams: [], uri: UriBuilder::createFromString(uri: 'http://example.com/')));

        $this->assertSame(expected: $response, actual: $result);
    }

    public function test_duplicate_stage_throws() : void
    {
        $container = new FakeContainer;
        $logger    = new NullLogger;
        $chain     = new StageChain(container: $container, logger: $logger);

        $this->expectException(exception: StageOrderException::class);

        $chain->create(
            stages    : [SampleStage::class, SampleStage::class],
            middleware: [SampleMiddleware::class],
            core      : fn(Request $request) : ResponseInterface => $this->createMock(ResponseInterface::class)
        );
    }

    public function test_stage_placed_in_middleware_throws() : void
    {
        $container = new FakeContainer;
        $logger    = new NullLogger;
        $chain     = new StageChain(container: $container, logger: $logger);

        $this->expectException(exception: StageOrderException::class);

        $chain->create(
            stages    : [],
            middleware: [SampleStage::class],
            core      : fn(Request $request) : ResponseInterface => $this->createMock(ResponseInterface::class)
        );
    }
}

final class FakeContainer implements ContainerInterface
{
    public function get(string $id)
    {
        return new $id;
    }

    public function has(string $id) : bool
    {
        return class_exists($id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        throw new RuntimeException(message: 'Not implemented.');
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        throw new RuntimeException(message: 'Not implemented.');
    }

    public function injectInto(object $target) : object
    {
        throw new RuntimeException(message: 'Not implemented.');
    }

    public function canInject(object $target) : bool
    {
        return false;
    }

    public function beginScope() : void {}

    public function endScope() : void {}

    public function instance(string $abstract, object $instance) : void
    {
        throw new RuntimeException(message: 'Not implemented.');
    }
}

final class SampleStage implements RouteStage
{
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        return $next($request);
    }
}

final class SampleMiddleware
{
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        return $next($request);
    }
}
