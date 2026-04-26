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

    use components\Container\DI\Capabilities\Composition\CreateContainerConfig;
    use components\Container\DI\Container;
    use components\Container\DI\Flows\CreateContainer\CreateContainer;

    $root             = dirname(path: __DIR__) . '/DI';
    $composerAutoload = dirname(path: __DIR__, levels: 2) . '/vendor/autoload.php';
    if (is_file(filename: $composerAutoload)) {
        require_once $composerAutoload;
    }

    spl_autoload_register(
        callback: static function (string $class) use ($root) : void {
            $prefix = 'Avax\\Container\\DI\\';
            if (! str_starts_with(haystack: $class, needle: $prefix)) {
                return;
            }

            $relative = substr(string: $class, offset: strlen(string: $prefix));
            $path     = $root . '/' . str_replace(search: '\\', replace: '/', subject: $relative) . '.php';

            if (is_file(filename: $path)) {
                require_once $path;
            }
        }
    );

    function assertTrue(bool $condition, string $message) : void
    {
        if (! $condition) {
            throw new RuntimeException(message: $message);
        }
    }

    function assertSame(mixed $expected, mixed $actual, string $message) : void
    {
        if ($expected !== $actual) {
            throw new RuntimeException(
                message: $message . ' Expected ' . var_export(value: $expected, return: true) . ' but got ' . var_export(value: $actual, return: true) . '.'
            );
        }
    }

    function assertNotSame(mixed $expected, mixed $actual, string $message) : void
    {
        if ($expected === $actual) {
            throw new RuntimeException(message: $message);
        }
    }

    function assertInstanceOf(string $expectedClass, mixed $value, string $message) : void
    {
        if (! $value instanceof $expectedClass) {
            throw new RuntimeException(message: $message . ' Expected instance of ' . $expectedClass . '.');
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
                message : $message . ' Expected ' . $expectedClass . ' but got ' . $throwable::class . '.',
                code    : 0,
                previous: $throwable
            );
        }

        throw new RuntimeException(message: $message . ' Expected ' . $expectedClass . ' but nothing was thrown.');
    }

    function makeTestContainer(
        CreateContainerConfig|null $config = null
    ) : Container
    {
        return new CreateContainer()->create(config: $config);
    }
}
