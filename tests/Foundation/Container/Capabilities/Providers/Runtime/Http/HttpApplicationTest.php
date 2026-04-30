<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Capabilities\Providers\Runtime\Http;

use Avax\Components\Application\Container\Core\AppFactory;
use Avax\Components\Application\Container\DependencyInjection\Capability\Providers\Runtime\Http\MiddlewareBaseRegisterDependency;
use Avax\Components\Application\Container\DependencyInjection\Capability\Providers\Runtime\Http\RouterBaseRegisterDependency;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Response\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Tests\TestCase;
use LogicException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

final class HttpApplicationTest extends TestCase
{
    public function test_run_boots_scope_dispatches_router_and_terminates() : void
    {
        $backup = [
            '_SERVER' => $_SERVER ?? [],
            '_GET'    => $_GET ?? [],
            '_POST'   => $_POST ?? [],
            '_COOKIE' => $_COOKIE ?? [],
            '_FILES'  => $_FILES ?? [],
        ];

        /** @var ResponseInterface|null $response */
        $response = null;
        $content  = '';

        try {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['REQUEST_URI']    = '/';
            $_SERVER['HTTP_HOST']      = 'localhost';
            $_SERVER['SERVER_NAME']    = 'localhost';
            $_SERVER['SERVER_PORT']    = '80';
            $_SERVER['QUERY_STRING']   = '';
            $_GET                      = [];
            $_POST                     = [];
            $_COOKIE                   = [];
            $_FILES                    = [];

            $app = AppFactory::http(
                providers: [MiddlewareBaseRegisterDependency::class, RouterBaseRegisterDependency::class],
                routes   : $this->createRoutesFile(),
                cacheDir : sys_get_temp_dir(),
                debug    : true
            );

            $app->getContainer()->instance(abstract: LoggerInterface::class, instance: new NullLogger);
            $app->getContainer()->instance(abstract: RouterRuntimeInterface::class, instance: new FakeRouter);

            ob_start();
            $response = $app->run();
            $content  = (string) ob_get_clean();
        } finally {
            $_SERVER = $backup['_SERVER'];
            $_GET    = $backup['_GET'];
            $_POST   = $backup['_POST'];
            $_COOKIE = $backup['_COOKIE'];
            $_FILES  = $backup['_FILES'];
        }

        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        $this->assertSame(expected: 200, actual: $response->getStatusCode());
        $this->assertStringContainsString(needle: 'Avax components router is up.', haystack: $content);
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

final class FakeRouter implements RouterRuntimeInterface
{
    public function resolve(ServerRequest $request) : ResponseInterface
    {
        return Response::text(content: 'Avax components router is up.');
    }

    public function getRouteByName(string $name) : RouteDefinition
    {
        throw new RuntimeException(message: 'Not implemented in fake router');
    }

    public function allRoutes() : array
    {
        return [];
    }
}
