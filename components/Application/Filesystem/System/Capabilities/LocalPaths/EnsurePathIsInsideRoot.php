<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\LocalPaths;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\PathTraversalAttempt;

final readonly class EnsurePathIsInsideRoot
{
    public function execute(string $path, string $root) : void
    {
        $realRoot = realpath($root);

        if ($realRoot === false) {
            return;
        }

        $realPath = realpath(dirname($root) . '/' . ltrim($path, '/'));

        if ($realPath !== false && ! str_starts_with($realPath, $realRoot)) {
            throw new PathTraversalAttempt($path);
        }
    }
}