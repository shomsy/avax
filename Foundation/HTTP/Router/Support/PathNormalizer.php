<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

/**
 * Normalizes URL paths for consistent route matching.
 *
 * Ensures that paths like `/users/` and `/users` are treated as equivalent,
 * preventing routing inconsistencies and improving user experience.
 */
final class PathNormalizer
{
    /**
     * Normalizes a URL path by:
     * - Ensuring leading slash
     * - Removing trailing slashes (except for root path)
     * - Collapsing multiple consecutive slashes
     * - Handling edge cases safely
     *
     * @param string $path The path to normalize
     *
     * @return string The normalized path
     */
    public static function normalize(string $path) : string
    {
        // Handle empty path
        if ($path === '') {
            return '/';
        }

        // Collapse multiple slashes and normalize
        $path = preg_replace('#/+#', '/', $path);

        if ($path === null) {
            throw new \InvalidArgumentException('Invalid path format for normalization');
        }

        // Ensure leading slash
        if (! str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        // Remove trailing slash unless it's the root path
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * Checks if two paths are equivalent after normalization.
     *
     * @param string $path1 First path to compare
     * @param string $path2 Second path to compare
     *
     * @return bool True if paths are equivalent
     */
    public static function areEquivalent(string $path1, string $path2) : bool
    {
        return self::normalize($path1) === self::normalize($path2);
    }
}