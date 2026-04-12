<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

final readonly class StartedFederatedLogin
{
    public function __construct(
        public string $redirectUrl,
        public string|null $state = null
    ) {}
}
