<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Body;

use Stringable;

final readonly class StreamBody implements Stringable
{
    public function __construct(
        private string $content = '',
    ) {
    }

    public function getContents(): string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
