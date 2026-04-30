<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Body;

final class RawBody
{
    public function __construct(
        private string $content,
    ) {}

    public function toString(): string
    {
        return $this->content;
    }
}
