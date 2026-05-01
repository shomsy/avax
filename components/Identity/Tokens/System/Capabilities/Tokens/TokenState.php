<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens;

final readonly class TokenState
{
    public function __construct(
        public string $value,
    ) {}
}
