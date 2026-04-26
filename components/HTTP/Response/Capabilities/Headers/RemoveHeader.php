<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

use SensitiveParameter;

final class RemoveHeader
{
    public function __invoke(#[SensitiveParameter] ResponseHeaders $headers, string $name) : ResponseHeaders
    {
        return $headers->remove(name: $name);
    }
}
