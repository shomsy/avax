<?php

declare(strict_types=1);

namespace Avax\Container\Tests;

use Avax\Container\Features\Operate\Boot\Application;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\Router\Router;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/ApplicationLifecycleTest.md#quick-summary
 */
final class ApplicationLifecycleTest extends TestCase
{
    public function testRunBootsScopeDispatchesRouterAndTerminates(): void
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

            $app = Application::start(root: $root)
                ->exposeWeb(path: $root . '/routes/web.php')
                ->exposeApi(path: $root . '/routes/api.php')
                ->build();

            $app->getContainer()->instance(abstract: Router::class, instance: new FakeRouter());

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

final class FakeRouter implements RouterInterface
{
    public function post(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function get(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        throw new LogicException(message: 'Fake router does not support route registration.');
    }

    public function put(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function patch(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function delete(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function options(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function head(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->get(path: $path, action: $action);
    }

    public function any(string $path, callable|array|string $action): array
    {
        throw new LogicException(message: 'Fake router does not support wildcard registration.');
    }

    public function fallback(callable|array|string $handler): void
    {
        throw new LogicException(message: 'Fake router does not support fallback.');
    }

    public function resolve(Request $request): ResponseInterface
    {
        return new Response(
            stream: Stream::fromString(content: 'Avax components router is up.'),
            statusCode: 200,
            headers: ['Content-Type' => 'text/plain']
        );
    }
}
