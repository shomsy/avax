<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\LocalPaths;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\PathTraversalAttempt;

final readonly class RejectPathTraversal
{
    private const FORBIDDEN_PATTERNS
        = [
            '..',
            "\0",
            "\n",
            "\r",
        ];

    public function execute(string $path) : void
    {
        $cleanPath = str_replace(["\\", "//"], "/", $path);

        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            if (str_contains($cleanPath, $pattern)) {
                if ($pattern === '..') {
                    $parts = explode('/', $cleanPath);
                    foreach ($parts as $part) {
                        if ($part === '..') {
                            throw new PathTraversalAttempt($path);
                        }
                    }
                } else {
                    throw new PathTraversalAttempt($path);
                }
            }
        }

        if (str_starts_with($cleanPath, '~')) {
            throw new PathTraversalAttempt($path);
        }
    }
}