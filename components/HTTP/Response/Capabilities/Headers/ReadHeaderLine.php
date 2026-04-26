<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

use SensitiveParameter;

final class ReadHeaderLine
{
    public function __invoke(#[SensitiveParameter] ResponseHeaders $headers, string $name) : string
    {
        return $headers->readLine(name: $name);
    }
}
