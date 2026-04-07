<?php

declare(strict_types=1);

namespace Psr\Container {
    interface ContainerInterface
    {
        public function get(string $id): mixed;

        public function has(string $id): bool;
    }

    interface ContainerExceptionInterface extends \Throwable
    {
    }

    interface NotFoundExceptionInterface extends ContainerExceptionInterface
    {
    }
}

namespace {
    $root = dirname(__DIR__);
    $priority = [
        $root . '/DependencyInjection/Registrations/ServiceRegistryInterface.php' => 0,
        $root . '/DependencyInjection/Scopes/ScopeInterface.php' => 10,
        $root . '/DependencyInjection/Injection/InjectionReport.php' => 20,
        $root . '/ContainerInterface.php' => 30,
        $root . '/DependencyInjection/Providers/ServiceProviderInterface.php' => 40,
        $root . '/DependencyInjection/Errors/ContainerException.php' => 50,
        $root . '/DependencyInjection/Errors/ServiceNotFoundException.php' => 60,
        $root . '/Container.php' => 1000,
    ];

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        $path = $file->getPathname();
        if ($file->getExtension() !== 'php') {
            continue;
        }
        if (str_contains($path, '/.agents/') || str_contains($path, '/tests/')) {
            continue;
        }
        $files[] = $path;
    }

    usort(
        $files,
        static function (string $left, string $right) use ($priority) : int {
            $leftPriority = $priority[$left] ?? 100;
            $rightPriority = $priority[$right] ?? 100;

            if ($leftPriority !== $rightPriority) {
                return $leftPriority <=> $rightPriority;
            }

            return $left <=> $right;
        }
    );

    foreach ($files as $file) {
        require_once $file;
    }

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
        \Avax\Container\DependencyInjection\Configuration\CreateContainerConfig|null $config = null
    ) : \Avax\Container\Container {
        return (new \Avax\Container\DependencyInjection\CreateContainer())->create(config: $config);
    }
}
