<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Capabilities\Parts;

use Stringable;

final readonly class Path implements Stringable
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = '/' . ltrim($path, '/');
    }

    public function __toString() : string
    {
        return $this->path;
    }
}
