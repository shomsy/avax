<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

final class HasHeader
{
    public function __invoke(ResponseHeaders $headers, string $name) : bool
    {
        return $headers->has(name: $name);
    }
}
