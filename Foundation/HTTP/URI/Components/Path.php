<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Components;

/**
 * Represents a URI path (e.g., /path/to/resource).
 *
 * A final class to ensure immutability and integrity of URI path components
 * throughout the application, preventing inheritance or modification.
 */
final readonly class Path implements \Stringable
{
    /**
     * The normalized path string.
     */
    private string $path;

    /**
     * Constructor.
     *
     * @param string $path The URI path to normalize and store.
     *
     * Ensures the path is normalized upon instantiation to maintain consistency
     * and avoid dealing with non-normalized paths later in the usage.
     */
    public function __construct(string $path)
    {
        $this->path = $this->normalize(path: $path);
    }

    /**
     * Normalizes a path.
     *
     * @param string $path The path to normalize.
     *
     * @return string The normalized path, ensuring segments like ".." and "." are properly handled.
     */
    private function normalize(string $path) : string
    {
        $segments   = explode(separator: '/', string: $path);
        $normalized = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                // Skip empty segments and current directory markers.
                continue;
            }
            if ($segment === '.') {
                // Skip empty segments and current directory markers.
                continue;
            }
            if ($segment === '..') {
                // Remove the last segment for parent directory markers.
                array_pop(array: $normalized);
            } else {
                // Encode the segment to ensure it's safe for URLs.
                $normalized[] = rawurlencode(string: $segment);
            }
        }

        return '/' . implode(separator: '/', array: $normalized);
    }

    /**
     * Normalizes a path intended for file systems.
     *
     * @param string $path The file system path to normalize.
     *
     * @return string The normalized path with Windows file paths converted to Unix format.
     *
     * Handles specific file path converted to be compatible with URI paths.
     */
    public function normalizeForFile(string $path) : string
    {
        if (preg_match(pattern: '#^[a-zA-Z]:\\\\#', subject: $path)) {
            // Convert Windows paths to Unix format.
            $path = '/' . str_replace(search: '\\', replace: '/', subject: ltrim(string: $path, characters: '/'));
        }

        return $this->normalize(path: $path);
    }

    /**
     * Converts the Path object to a string.
     *
     * @return string The normalized path as a string.
     */
    public function __toString() : string
    {
        return $this->path;
    }
}