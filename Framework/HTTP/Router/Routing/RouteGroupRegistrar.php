<?php

declare(strict_types=1);

namespace Gemini\HTTP\Router\Routing;

use CallbackFilterIterator;
use FilesystemIterator;
use Gemini\HTTP\Router\Router;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;

final class RouteGroupRegistrar
{
    /**
     * Recursively loads and registers all route definition files from a given base directory.
     *
     * Each route file should use `$router = app(Router::class);` to register routes directly.
     *
     * @param string $baseDir
     *
     * @throws LogicException
     */
    public function registerFromDirectory(string $baseDir) : void
    {
        $this->ensureDirectoryIsValid(baseDir: $baseDir);

        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(directory: $baseDir, flags: FilesystemIterator::SKIP_DOTS)
        );

        $files = iterator_to_array(
            new CallbackFilterIterator(
                iterator: $iterator,
                callback: static fn(SplFileInfo $file) => $file->isFile() && $file->getExtension() === 'php'
            )
        );

        foreach ($files as $file) {
            $router = app(abstract: Router::class);

            try {
                (static function () use ($file, $router) {
                    require $file->getPathname();
                })();

                $buffered = Router::flushBuffered();

                if (empty($buffered)) {
                    echo "⚠️  [{$file->getFilename()}] did not register any routes.\n";
                    continue;
                }

                foreach ($buffered as $builder) {
                    $router->registerRoute($builder);
                }
            } catch (Throwable $e) {
                throw new RuntimeException(
                    message : "Failed to load route file [{$file->getFilename()}]: {$e->getMessage()}",
                    code    : 0,
                    previous: $e
                );
            }
        }
    }

    /**
     * Ensures the routes directory exists and is readable.
     *
     * @param string $baseDir
     *
     * @throws LogicException
     */
    private function ensureDirectoryIsValid(string $baseDir) : void
    {
        if (! is_dir($baseDir) || ! is_readable($baseDir)) {
            throw new LogicException(message: "Routes directory '{$baseDir}' does not exist or is not readable.");
        }
    }
}
