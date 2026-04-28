<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Paths;

/**
 * Normalizes URL paths for consistent route matching.
 */
final class PathNormalizer
{
    public static function areEquivalent(string $path1, string $path2) : bool
    {
        return self::normalize($path1) === self::normalize($path2);
    }

    public static function normalize(string $path) : string
    {
        if ($path === '') return '/';

        $path = preg_replace('#/+#', '/', $path);
        if ($path === null) return '/';

        if (! str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}
