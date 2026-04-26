<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Sessions;

final readonly class SessionState
{
    public function __construct(
        public string $value
    ) {}
}
