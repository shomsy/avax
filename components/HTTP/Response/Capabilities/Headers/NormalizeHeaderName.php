<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Headers;

final class NormalizeHeaderName
{
    public function __invoke(string $name) : string
    {
        $validated = new ValidateHeaderName()(name: $name);

        return strtolower(string: $validated);
    }
}
