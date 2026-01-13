<?php

declare(strict_types=1);

namespace Avax\Container\Tests;

use Avax\Container\Core\AppFactory;
use Avax\Container\Http\HttpApplication;
use Avax\Container\Providers\HTTP\MiddlewareServiceProvider;
use Avax\Container\Providers\HTTP\RouterServiceProvider;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/ApplicationRegistrationTest.md#quick-summary
 */
final class ApplicationRegistrationTest extends TestCase
{
    private HttpApplication $app;

    public function test_singleton_registration() : void
    {
        $this->app->getContainer()->singleton(abstract: 'shared_service', concrete: static function () {
            return new stdClass;
        });

        $instance1 = $this->app->getContainer()->make(abstract: 'shared_service');
        $instance2 = $this->app->getContainer()->make(abstract: 'shared_service');

        $this->assertInstanceOf(expected: stdClass::class, actual: $instance1);
        $this->assertSame(expected: $instance1, actual: $instance2);
    }

    public function test_bind_registration() : void
    {
        $this->app->getContainer()->bind(abstract: 'transient_service', concrete: static function () {
            return new stdClass;
        });

        $instance1 = $this->app->getContainer()->make(abstract: 'transient_service');
        $instance2 = $this->app->getContainer()->make(abstract: 'transient_service');

        $this->assertNotSame(expected: $instance1, actual: $instance2);
    }

    public function test_scoped_registration() : void
    {
        $this->app->getContainer()->singleton(abstract: 'scoped_service', concrete: static function () {
            return new stdClass;
        });

        // Scope 1
        $this->app->getContainer()->beginScope();
        $instance1 = $this->app->getContainer()->make(abstract: 'scoped_service');
        $instance2 = $this->app->getContainer()->make(abstract: 'scoped_service');
        $this->assertSame(expected: $instance1, actual: $instance2);
        $this->app->getContainer()->endScope();

        // Scope 2
        $this->app->getContainer()->beginScope();
        $instance3 = $this->app->getContainer()->make(abstract: 'scoped_service');
        $this->assertNotSame(expected: $instance1, actual: $instance3);
        $this->app->getContainer()->endScope();
    }

    protected function setUp() : void
    {
        $root = dirname(__DIR__, 2);

        $this->app = AppFactory::http(
            providers: [MiddlewareServiceProvider::class, RouterServiceProvider::class],
            routes   : $root . '/Presentation/HTTP/routes/web.routes.php',
            cacheDir : $root . '/storage/cache',
            debug    : true
        );

        // Override router with a lightweight fake to keep tests deterministic.
        $this->app->getContainer()->instance(abstract: RouterInterface::class, instance: new RegistrationFakeRouter);

        $this->app->getContainer()->instance(abstract: LoggerInterface::class, instance: new NullLogger);
    }
}

final class RegistrationFakeRouter implements RouterInterface
{
    public function post(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function get(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        throw new LogicException(message: 'Fake router does not support route registration.');
    }

    public function put(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function patch(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function delete(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function options(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function head(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function any(string $path, callable|array|string $action) : array
    {
        throw new LogicException(message: 'Fake router does not support wildcard registration.');
    }

    public function fallback(callable|array|string $handler) : void
    {
        throw new LogicException(message: 'Fake router does not support fallback.');
    }

    public function resolve(Request $request) : ResponseInterface
    {
        throw new LogicException(message: 'Fake router does not resolve requests in registration tests.');
    }
}
