<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests\Unit;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Cache\RouteCacheLoader;
use Avax\HTTP\Router\Matching\RouteMatcherRegistry;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouterRegistrar;
use Avax\HTTP\Router\Support\RouteRegistry;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use PHPUnit\Framework\TestCase;
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
     * @throws \Avax\Contracts\FilesystemException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
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
        $router->registerRoute(method: 'GET', path: '/closure', action: static fn() => 'x');

        $runtime = new class($router) implements RouterRuntimeInterface {
            public function __construct(private HttpRequestRouter $router) {}

            public function resolve(Request $request) : ResponseInterface
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
        array_map('unlink', $files);
        @rmdir($dir);
    }
}