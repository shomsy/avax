<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

final class ReadHeaderLine
{
    public function __invoke(ResponseHeaders $headers, string $name) : string
    {
        return $headers->readLine(name: $name);
    }
}
