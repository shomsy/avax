<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Headers;

use SensitiveParameter;

final class ReplaceHeader
{
    public function __invoke(#[SensitiveParameter] ResponseHeaders $headers, string $name, mixed $value) : ResponseHeaders
    {
        return $headers->replace(name: $name, value: $value);
    }
}
