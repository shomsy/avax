<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Router\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\HTTP\Router\System\Flows\BootstrapRoutes\Cache\RouteCacheLoader;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouterRegistrar;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use Avax\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcherRegistry;
use Avax\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException;
use Avax\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use RuntimeException;

final class RouteCacheTest extends TestCase
{
    public function test_cache_write_and_load_round_trip() : void
    {
        $this->markTestSkipped(message: 'Test requires Storage facade in container');
    }

    /**
     * @throws ReservedRouteNameException
     * @throws DuplicateRouteException
     */
    public function test_cache_write_fails_when_only_closures() : void
    {
        $routesDir = sys_get_temp_dir() . '/router-cache-' . uniqid();
        $cachePath = $routesDir . '/routes.cache.php';

        mkdir(directory: $routesDir, permissions: 0777, recursive: true);
        file_put_contents(filename: $routesDir . '/sample.routes.php', data: "<?php // sentinel\n");

        $matcherRegistry = RouteMatcherRegistry::withDefaults(logger: new NullLogger);
        $matcher         = $matcherRegistry->get(key: 'domain');

        $router = new HttpRequestRouter(
            constraintValidator: new RouteConstraintValidator,
            matcher            : $matcher,
            logger             : new NullLogger
        );
        $router->registerRoute(method: 'GET', path: '/closure', action: static fn () => 'x');

        $runtime = new class($router) implements RouterRuntimeInterface {
            private HttpRequestRouter $router;

            public function __construct(HttpRequestRouter $router) { $this->router = $router; }

            public function resolve(ServerRequest $request) : ResponseInterface
            {
                throw new RuntimeException(message: 'Not used');
            }

            public function getRouteByName(string $name) : RouteDefinition
            {
                return $this->router->getByName(name: $name);
            }

            public function allRoutes() : array
            {
                return $this->router->allRoutes();
            }
        };

        $writer = new RouteCacheLoader(
            registrar: new RouterRegistrar(registry: new RouteRegistry, httpRequestRouter: $router),
            router   : $runtime,
            logger   : new NullLogger
        );

        $this->expectException(exception: RuntimeException::class);
        $writer->write(cachePath: $cachePath, routesPath: $routesDir);

        $this->cleanupDirectory(dir: $routesDir);
    }

    private function cleanupDirectory(string $dir) : void
    {
        $files = glob(pattern: $dir . '/*') ?: [];
        array_map(callback: 'unlink', array: $files);
        rmdir(directory: $dir);
    }
}
