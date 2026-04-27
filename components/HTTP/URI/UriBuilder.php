<?php

declare(strict_types=1);

namespace Avax\HTTP\URI;

use Psr\Http\Message\UriInterface;

final readonly class UriBuilder
{
    public static function createFromString(string $uri): UriInterface
    {
        return Uri::fromString(uri: $uri);
    }
}
