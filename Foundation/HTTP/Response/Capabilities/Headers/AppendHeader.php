<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

final class AppendHeader
{
    public function __invoke(ResponseHeaders $headers, string $name, mixed $value) : ResponseHeaders
    {
        return $headers->append(name: $name, value: $value);
    }
}
