<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Flows\RegisterBindings;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface as ComponentResponseInterface;
use Avax\HTTP\Router\RouterInterface;
use Avax\Tests\TestCase;
use components\Container\Core\AppFactory;
use components\Container\DependencyInjection\Capability\Providers\Runtime\Http\HttpApplication;
use components\Container\DependencyInjection\Capability\Providers\Runtime\Http\MiddlewareBaseRegisterDependency;
use components\Container\DependencyInjection\Capability\Providers\Runtime\Http\RouterBaseRegisterDependency;
use LogicException;
use Override;
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
            concrete: static fn (...$arguments) : stdClass => new stdClass,
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
            concrete: static fn (...$arguments) : stdClass => new stdClass,
        );

        $instance1 = $this->app->getContainer()->make(abstract: 'transient_service');
        $instance2 = $this->app->getContainer()->make(abstract: 'transient_service');

        $this->assertNotSame(expected: $instance1, actual: $instance2);
    }

    public function test_scoped_registration() : void
    {
        $this->app->getContainer()->scoped(
            abstract: 'scoped_service',
            concrete: static fn (...$arguments) : stdClass => new stdClass,
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

    #[Override]
    protected function setUp() : void
    {
        $this->app = AppFactory::http(
            providers: [MiddlewareBaseRegisterDependency::class, RouterBaseRegisterDependency::class],
            routes   : $this->createRoutesFile(),
            cacheDir : sys_get_temp_dir(),
            debug    : true,
        );

        $this->app->getContainer()->instance(abstract: RouterInterface::class, instance: new RegistrationFakeRouter);
        $this->app->getContainer()->instance(abstract: LoggerInterface::class, instance: new NullLogger);
    }

    private function createRoutesFile() : string
    {
        $file = tempnam(directory: sys_get_temp_dir(), prefix: 'avax-routes-');
        if ($file === false) {
            throw new LogicException(message: 'Unable to create temporary routes file.');
        }

        file_put_contents(filename: $file, data: "<?php\n");

        return $file;
    }
}

final class RegistrationFakeRouter implements RouterInterface
{
    public function get(string $u, mixed $a) : void
    {
        throw new LogicException(message: 'Fake router does not support route registration.');
    }

    public function post(string $u, mixed $a) : void
    {
        throw new LogicException(message: 'Fake router does not support route registration.');
    }

    public function dispatch(RequestInterface $r) : ComponentResponseInterface
    {
        throw new LogicException(message: 'Fake router does not resolve requests in registration tests.');
    }
}
