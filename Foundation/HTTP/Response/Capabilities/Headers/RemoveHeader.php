<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

final class RemoveHeader
{
    public function __invoke(ResponseHeaders $headers, string $name) : ResponseHeaders
    {
        return $headers->remove(name: $name);
    }
}
