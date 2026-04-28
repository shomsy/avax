<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Headers;

use SensitiveParameter;

final class HasHeader
{
    public function __invoke(#[SensitiveParameter] ResponseHeaders $headers, string $name) : bool
    {
        return $headers->has(name: $name);
    }
}
