<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\StartSession;

use Avax\Components\Identity\Capabilities\Sessions\IdentitySession;

final readonly class StartedSession
{
    public function __construct(private IdentitySession $session) {}

    public function session(): IdentitySession
    {
        return $this->session;
    }
}
