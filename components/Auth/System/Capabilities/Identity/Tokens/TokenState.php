<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens;

final readonly class TokenState
{
    public function __construct(
        public string $value
    ) {}
}
