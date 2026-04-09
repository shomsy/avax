<?php

declare(strict_types=1);

namespace Psr\Container {

    use Throwable;

    interface ContainerInterface
    {
        public function get(string $id) : mixed;

        public function has(string $id) : bool;
    }

    interface ContainerExceptionInterface extends Throwable {}

    interface NotFoundExceptionInterface extends ContainerExceptionInterface {}
}

namespace {

    use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
    use Avax\Container\DI\Container;
    use Avax\Container\DI\Flows\CreateContainer\CreateContainer;

    $root             = dirname(__DIR__) . '/DI';
    $composerAutoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
    if (is_file($composerAutoload)) {
        require_once $composerAutoload;
    }

    spl_autoload_register(
        static function (string $class) use ($root) : void {
            $prefix = 'Avax\\Container\\DI\\';
            if (! str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $path     = $root . '/' . str_replace('\\', '/', $relative) . '.php';

            if (is_file($path)) {
                require_once $path;
            }
        }
    );

    function assertTrue(bool $condition, string $message) : void
    {
        if (! $condition) {
            throw new RuntimeException($message);
        }
    }

    function assertSame(mixed $expected, mixed $actual, string $message) : void
    {
        if ($expected !== $actual) {
            throw new RuntimeException(
                $message . ' Expected ' . var_export($expected, true) . ' but got ' . var_export($actual, true) . '.'
            );
        }
    }

    function assertNotSame(mixed $expected, mixed $actual, string $message) : void
    {
        if ($expected === $actual) {
            throw new RuntimeException($message);
        }
    }

    function assertInstanceOf(string $expectedClass, mixed $value, string $message) : void
    {
        if (! $value instanceof $expectedClass) {
            throw new RuntimeException($message . ' Expected instance of ' . $expectedClass . '.');
        }
    }

    function assertThrows(string $expectedClass, callable $callback, string $message) : void
    {
        try {
            $callback();
        } catch (Throwable $throwable) {
            if ($throwable instanceof $expectedClass) {
                return;
            }

            throw new RuntimeException(
                $message . ' Expected ' . $expectedClass . ' but got ' . $throwable::class . '.',
                0,
                $throwable
            );
        }

        throw new RuntimeException($message . ' Expected ' . $expectedClass . ' but nothing was thrown.');
    }

    function makeTestContainer(
        CreateContainerConfig|null $config = null
    ) : Container
    {
        return (new CreateContainer())->create(config: $config);
    }
}
