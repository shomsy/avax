<?php

declare(strict_types=1);

namespace Avax\Container\Tests;

use Avax\Container\Core\AppFactory;
use Avax\Container\Providers\HTTP\MiddlewareServiceProvider;
use Avax\Container\Providers\HTTP\RouterServiceProvider;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\Routing\RouteDefinition;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/ApplicationLifecycleTest.md#quick-summary
 */
final class ApplicationLifecycleTest extends TestCase
{
    public function test_run_boots_scope_dispatches_router_and_terminates() : void
    {
        $root = dirname(__DIR__, 3);

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
                providers: [MiddlewareServiceProvider::class, RouterServiceProvider::class],
                routes   : $root . '/Presentation/HTTP/routes/web.routes.php',
                cacheDir : $root . '/storage/cache',
                debug    : true
            );

            $app->getContainer()->instance(
                abstract: LoggerInterface::class,
                instance: new NullLogger
            );

            $app->getContainer()->instance(
                abstract: RouterRuntimeInterface::class,
                instance: new FakeRouter
            );

            ob_start();
            $response = $app->run();
            $content  = ob_get_clean();
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
}

final class FakeRouter implements RouterRuntimeInterface
{
    public function resolve(Request $request) : ResponseInterface
    {
        return new Response(
            stream    : Stream::fromString(content: 'Avax components router is up.'),
            statusCode: 200,
            headers   : ['Content-Type' => 'text/plain']
        );
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
