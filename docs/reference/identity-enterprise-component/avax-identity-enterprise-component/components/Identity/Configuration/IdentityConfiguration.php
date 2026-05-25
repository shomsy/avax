<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration;

use Avax\Components\Identity\Foundation\Values\TokenSecret;

final readonly class IdentityConfiguration
{
    public function __construct(private TokenSecret $tokenSecret) {}

    public function tokenSecret(): TokenSecret
    {
        return $this->tokenSecret;
    }
}
