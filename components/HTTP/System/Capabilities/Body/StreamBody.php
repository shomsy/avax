<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Body;

final class StreamBody
{
    public function __construct(
        private string $content = '',
    ) {}

    public function getContents(): string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
