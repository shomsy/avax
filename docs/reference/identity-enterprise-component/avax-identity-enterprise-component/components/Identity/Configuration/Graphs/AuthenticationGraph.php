<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration\Graphs;

use Avax\Components\Identity\Flows\AuthenticatePassword\AuthenticatePassword;

final readonly class AuthenticationGraph
{
    public function __construct(private AuthenticatePassword $authenticatePassword) {}

    public function authenticatePassword(): AuthenticatePassword
    {
        return $this->authenticatePassword;
    }
}
