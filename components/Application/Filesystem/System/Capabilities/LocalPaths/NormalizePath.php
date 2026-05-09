<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\LocalPaths;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\PathTraversalAttempt;

final readonly class NormalizePath
{
    public function execute(string $path) : string
    {
        $path           = str_replace(["\\", "//"], "/", $path);
        $normalizedPath = preg_replace('#/+#', '/', $path);
        $cleanPath      = $normalizedPath ?? $path;

        if ($cleanPath === '') {
            return '/';
        }

        $parts = explode('/', $cleanPath);
        $stack = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($stack);
            } else {
                $stack[] = $part;
            }
        }

        $normalized = '/' . implode('/', $stack);

        if (str_starts_with($path, '/')) {
            return $normalized;
        }

        return ltrim($normalized, '/');
    }
}