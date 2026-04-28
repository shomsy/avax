<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\Parts;

use Stringable;

/**
 * Represents a URI path.
 */
final readonly class Path implements Stringable
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $this->normalize(path: $path);
    }

    private function normalize(string $path) : string
    {
        $segments   = explode('/', $path);
        $normalized = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            if ($segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($normalized);
            } else {
                $normalized[] = rawurlencode($segment);
            }
        }

        return '/' . implode('/', $normalized);
    }

    public function __toString() : string
    {
        return $this->path;
    }
}