<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\System\Capabilities\Headers;

final readonly class HeaderName
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = strtolower($name);
    }

    public function toString() : string
    {
        return $this->name;
    }
}
