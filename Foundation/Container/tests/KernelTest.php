<?php

declare(strict_types=1);

namespace Avax\Container\Operate\Boot {

    use RuntimeException;

    if (! function_exists(function: __NAMESPACE__ . '\config')) {
        function config(string $key, mixed $default = null): mixed
        {
            return null;
        }
    }

    if (! function_exists(function: __NAMESPACE__ . '\app')) {
        function app(): void
        {
            throw new RuntimeException(message: 'app() should not be called in this test.');
        }
    }
}

namespace Avax\Container\Tests {

    use Avax\Container\Features\Operate\Boot\Kernel;
    use Avax\HTTP\Request\Request;
    use Avax\HTTP\Router\RouterInterface;
    use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
    use Avax\Logging\ErrorHandler;
    use LogicException;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Log\LoggerInterface;
    use ReflectionException;
    use ReflectionMethod;
    use RuntimeException;
    use Stringable;

    /**
     * PHPUnit test coverage for Container component behavior.
     *
     * @see docs_md/tests/KernelTest.md#quick-summary
     */
    final class KernelTest extends TestCase
    {
        /**
         * @throws ReflectionException
         */
        public function testKernelMiddlewareConfigFallbacksToEmptyArray(): void
        {
            $kernel = new Kernel(router: new DummyRouter(), errorHandler: new ErrorHandler(logger: new DummyLogger()));

            $method = new ReflectionMethod(objectOrMethod: $kernel, method: 'resolveConfiguredMiddlewares');
            $method->setAccessible(accessible: true);

            $middlewares = $method->invoke(object: $kernel);

            $this->assertSame(expected: [], actual: $middlewares);
        }

        /**
         * @throws ReflectionException
         */
        public function testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray(): void
        {
            $kernel = new Kernel(
                router: new DummyRouter(),
                errorHandler: new ErrorHandler(logger: new DummyLogger()),
                configResolver: static function (): never {
                    throw new RuntimeException(message: 'config fail');
                }
            );

            $method = new ReflectionMethod(objectOrMethod: $kernel, method: 'resolveConfiguredMiddlewares');
            $method->setAccessible(accessible: true);

            $middlewares = $method->invoke(object: $kernel);

            $this->assertSame(expected: [], actual: $middlewares);
        }
    }

    final class DummyRouter implements RouterInterface
    {
        public function get(string $path, callable|array|string $action): RouteRegistrarProxy
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function post(string $path, callable|array|string $action): RouteRegistrarProxy
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function put(string $path, callable|array|string $action): RouteRegistrarProxy
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function patch(string $path, callable|array|string $action): RouteRegistrarProxy
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function delete(string $path, callable|array|string $action): RouteRegistrarProxy
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function options(string $path, callable|array|string $action): RouteRegistrarProxy
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function head(string $path, callable|array|string $action): RouteRegistrarProxy
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function any(string $path, callable|array|string $action): array
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function fallback(callable|array|string $handler): void
        {
            throw new LogicException(message: 'Not used in this test.');
        }

        public function resolve(Request $request): ResponseInterface
        {
            throw new LogicException(message: 'Not used in this test.');
        }
    }

    final class DummyLogger implements LoggerInterface
    {
        public function emergency(string|Stringable $message, array $context = []): void {}

        public function alert(string|Stringable $message, array $context = []): void {}

        public function critical(string|Stringable $message, array $context = []): void {}

        public function error(string|Stringable $message, array $context = []): void {}

        public function warning(string|Stringable $message, array $context = []): void {}

        public function notice(string|Stringable $message, array $context = []): void {}

        public function info(string|Stringable $message, array $context = []): void {}

        public function debug(string|Stringable $message, array $context = []): void {}

        public function log($level, string|Stringable $message, array $context = []): void {}
    }
}
