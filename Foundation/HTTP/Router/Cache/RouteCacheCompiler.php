<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Cache;

use Avax\HTTP\Router\Support\RouteCollector;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use RuntimeException;
use SplFileInfo;

/**
 * A service responsible for compiling application routes into a single cache file.
 * This compiler improves performance by preloading all the route definitions
 * during runtime instead of dynamically loading them.
 *
 * Supports the serialization of Closure-based route actions using Laravel\SerializableClosure.
 */
final readonly class RouteCacheCompiler
{
    /**
     * Compiles route files from a specific directory into a unified PHP cache file.
     *
     * This process consists of discovering route definition files (`*.routes.php`),
     * invoking all buffered route builders, serializing binding logic, and finally
     * writing the compiled routes in a compact serialized format for subsequent execution.
     *
     * @param string $directory  The absolute path to the directory containing `*.routes.php` files.
     * @param string $outputFile The absolute file path where the compiled routes cache will be stored.
     *
     * @return void
     *
     * @throws RuntimeException                   Thrown in cases where route compilation fails
     *                                            (e.g., no routes are defined, or file I/O fails).
     * @throws PhpVersionNotSupportedException    Thrown when the PHP version does not support
     *                                            Closure serialization methods used.
     */
    public function compile(string $directory, string $outputFile) : void
    {
        // Initialize an empty array to hold serialized routes.
        $routes = [];

        // Iterate over every route file within the provided directory.
        foreach ($this->getRouteFilesFromDirectory(baseDir: $directory) as $file) {
            // Evaluate the route definition file to register its routes with the collector.
            require $file->getPathname();

            // Retrieve and flush buffered RouteBuilder instances from the RouteCollector.
            $builders = RouteCollector::flushBuffered();

            // If no builders are registered, skip this file.
            if (empty($builders)) {
                continue;
            }

            // Traverse each RouteBuilder, compiling their route definitions.
            foreach ($builders as $builder) {
                // Compile each route into a directive that also serializes the action logic.
                foreach ($builder->build() as $route) {
                    // Serialize the route after preparing it with a serialized action.
                    $routes[] = serialize(value: $route->withSerializedAction());
                }
            }
        }

        // If no routes have been registered across any of the files, throw an exception.
        if (empty($routes)) {
            throw new RuntimeException(message: 'No routes were registered. Check your route files.');
        }

        // Generate the content for the PHP cache file containing all compiled routes.
        $cacheContent = $this->generateCacheFileContent(serializedRoutes: $routes);

        // Attempt to write the generated cache content to the specified output file.
        if (! file_put_contents(filename: $outputFile, data: $cacheContent)) {
            throw new RuntimeException(message: "Failed to write route cache to: {$outputFile}");
        }
    }

    /**
     * Discovers all route definition files within the provided directory.
     *
     * This method searches for files matching the naming pattern `*.routes.php`
     * and converts their file paths into SplFileInfo objects for further processing.
     *
     * @param string $baseDir The base directory path in which to scan for route definition files.
     *
     * @return list<SplFileInfo> A list of SplFileInfo objects representing discovered route files.
     *
     * @throws RuntimeException Thrown when the directory is inaccessible or unreadable.
     */
    private function getRouteFilesFromDirectory(string $baseDir) : array
    {
        // Verify that the provided base directory is both accessible and readable.
        if (! is_dir(filename: $baseDir) || ! is_readable(filename: $baseDir)) {
            throw new RuntimeException(message: "Routes directory '{$baseDir}' is not accessible.");
        }

        // Use glob to find all PHP files adhering to the "*.routes.php" pattern.
        $files = glob(pattern: "{$baseDir}/*.routes.php");

        // Convert each file path into an SplFileInfo instance and return the resulting array.
        return array_map(
            callback: static fn(string $path) => new SplFileInfo(filename: $path),
            array   : $files ?: [] // Default to an empty array if no files match.
        );
    }

    /**
     * Generates the PHP code to be written in the cache file.
     *
     * Given an array of serialized route definitions, this method composes the final
     * PHP content that will be saved. The resulting file contains an associative array
     * with each serialized route deserialized at runtime upon inclusion.
     *
     * @param array<string> $serializedRoutes A list of serialized route definitions.
     *
     * @return string The resultant PHP file's content as a string.
     */
    private function generateCacheFileContent(array $serializedRoutes) : string
    {
        // Start the creation of cache content with a PHP opening tag and comments.
        $code = "<?php\n\n/** Auto-generated route cache. Do not edit manually. */\n\nreturn [\n";

        // Append each serialized route using unserialize function calls.
        foreach ($serializedRoutes as $route) {
            // Escape single quotes to ensure code safety within double-quote strings.
            $escaped = str_replace(search: "'", replace: "\\'", subject: $route);

            // Append the unserialized route definition to the array.
            $code .= "    unserialize('{$escaped}'),\n";
        }

        // Close the array and return the complete PHP content as a string.
        $code .= "];\n";

        return $code;
    }
}