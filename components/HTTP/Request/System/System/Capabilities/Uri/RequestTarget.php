<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\System\Capabilities\Uri;

final readonly class RequestTarget
{
    public function __construct(
        private string $target,
    ) {}

    public function toString() : string
    {
        return $this->target;
    }
}
