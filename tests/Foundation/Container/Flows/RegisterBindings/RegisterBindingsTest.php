<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Flow\RegisterBindings;

use Avax\Container\DependencyInjection\Capability\Providers\Runtime\Http\HttpApplication;
use Avax\Container\DependencyInjection\Capability\Providers\Runtime\Http\MiddlewareServiceProvider;
use Avax\Container\DependencyInjection\Capability\Providers\Runtime\Http\RouterServiceProvider;
use Avax\Container\DependencyInjection\Configuration\AppFactory;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use stdClass;

final class RegisterBindingsTest extends TestCase
{
    private HttpApplication $app;

    public function test_singleton_registration() : void
    {
        $this->app->getContainer()->singleton(
            abstract: 'shared_service',
            concrete: static fn(...$arguments) : stdClass => new stdClass
        );

        $instance1 = $this->app->getContainer()->make(abstract: 'shared_service');
        $instance2 = $this->app->getContainer()->make(abstract: 'shared_service');

        $this->assertInstanceOf(expected: stdClass::class, actual: $instance1);
        $this->assertSame(expected: $instance1, actual: $instance2);
    }

    public function test_bind_registration() : void
    {
        $this->app->getContainer()->bind(
            abstract: 'transient_service',
            concrete: static fn(...$arguments) : stdClass => new stdClass
        );

        $instance1 = $this->app->getContainer()->make(abstract: 'transient_service');
        $instance2 = $this->app->getContainer()->make(abstract: 'transient_service');

        $this->assertNotSame(expected: $instance1, actual: $instance2);
    }

    public function test_scoped_registration() : void
    {
        $this->app->getContainer()->scoped(
            abstract: 'scoped_service',
            concrete: static fn(...$arguments) : stdClass => new stdClass
        );

        $this->app->getContainer()->beginScope();
        $instance1 = $this->app->getContainer()->make(abstract: 'scoped_service');
        $instance2 = $this->app->getContainer()->make(abstract: 'scoped_service');
        $this->assertSame(expected: $instance1, actual: $instance2);
        $this->app->getContainer()->endScope();

        $this->app->getContainer()->beginScope();
        $instance3 = $this->app->getContainer()->make(abstract: 'scoped_service');
        $this->assertNotSame(expected: $instance1, actual: $instance3);
        $this->app->getContainer()->endScope();
    }

    protected function setUp() : void
    {
        $this->app = AppFactory::http(
            providers: [MiddlewareServiceProvider::class, RouterServiceProvider::class],
            routes   : $this->createRoutesFile(),
            cacheDir : sys_get_temp_dir(),
            debug    : true
        );

        $this->app->getContainer()->instance(abstract: RouterInterface::class, instance: new RegistrationFakeRouter);
        $this->app->getContainer()->instance(abstract: LoggerInterface::class, instance: new NullLogger);
    }

    private function createRoutesFile() : string
    {
        $file = tempnam(sys_get_temp_dir(), 'avax-routes-');
        if ($file === false) {
            throw new LogicException(message: 'Unable to create temporary routes file.');
        }

        file_put_contents($file, "<?php\n");

        return $file;
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
